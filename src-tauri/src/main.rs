#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

mod lifecycle;
mod sidecar;

use lifecycle::LifecycleState;
use tauri::webview::WebviewWindowBuilder;
use tauri::{Manager, RunEvent, WebviewUrl};

fn main() {
    tauri::Builder::default()
        .manage(LifecycleState::new())
        .setup(|app| {
            let app_dir = app.path().resolve("..", tauri::path::BaseDirectory::Resource)?;
            let lifecycle = app.state::<LifecycleState>();
            let launch = lifecycle.start(&app_dir)?;

            let window = WebviewWindowBuilder::new(
                app,
                "main",
                WebviewUrl::External(launch.base_url.parse()?),
            )
            .title("Paraglide")
            .inner_size(1280.0, 800.0)
            .min_inner_size(1024.0, 600.0)
            .build()?;

            window.show()?;

            Ok(())
        })
        .build(tauri::generate_context!())
        .expect("error while running tauri application")
        .run(|app, event| {
            if matches!(event, RunEvent::ExitRequested { .. } | RunEvent::Exit) {
                let lifecycle = app.state::<LifecycleState>();
                lifecycle.shutdown();
            }
        });
}
