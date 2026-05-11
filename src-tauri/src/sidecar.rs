use std::net::{TcpListener, TcpStream};
use std::path::{Path, PathBuf};
use std::process::{Child, Command, Stdio};
use std::thread;
use std::time::{Duration, Instant};

use thiserror::Error;

#[derive(Debug, Clone, serde::Serialize)]
pub struct SidecarLaunch {
    pub base_url: String,
    pub port: u16,
    pub ollama_available: bool,
}

#[derive(Debug)]
pub struct SidecarHandle {
    child: Child,
}

impl SidecarHandle {
    pub fn stop(mut self) -> Result<(), SidecarError> {
        #[cfg(unix)]
        {
            use nix::sys::signal::{kill, Signal};
            use nix::unistd::Pid;

            let _ = kill(Pid::from_raw(self.child.id() as i32), Signal::SIGTERM);

            let deadline = Instant::now() + Duration::from_secs(5);
            while Instant::now() < deadline {
                if self.child.try_wait()?.is_some() {
                    return Ok(());
                }

                thread::sleep(Duration::from_millis(100));
            }
        }

        let _ = self.child.kill();
        let _ = self.child.wait();
        Ok(())
    }
}

#[derive(Debug, Error)]
pub enum SidecarError {
    #[error("unable to reserve localhost port")]
    PortReservation,
    #[error("unable to start FrankenPHP process: {0}")]
    Spawn(#[from] std::io::Error),
}

pub fn launch_frankenphp(app_dir: &Path) -> Result<(SidecarLaunch, SidecarHandle), SidecarError> {
    let listener = TcpListener::bind("127.0.0.1:0").map_err(|_| SidecarError::PortReservation)?;
    let port = listener.local_addr().map_err(|_| SidecarError::PortReservation)?.port();
    drop(listener);

    let public_dir = app_dir.join("app/public");
    let frankenphp = resolve_frankenphp_binary(app_dir);

    let child = Command::new(frankenphp)
        .arg("php-server")
        .arg("--root")
        .arg(public_dir)
        .arg("--listen")
        .arg(format!("127.0.0.1:{port}"))
        .stdout(Stdio::null())
        .stderr(Stdio::null())
        .spawn()?;

    let launch = SidecarLaunch {
        base_url: format!("http://127.0.0.1:{port}"),
        port,
        ollama_available: detect_ollama(),
    };

    Ok((launch, SidecarHandle { child }))
}

fn resolve_frankenphp_binary(app_dir: &Path) -> PathBuf {
    if let Ok(path) = std::env::var("PARAGLIDE_FRANKENPHP_BIN") {
        return PathBuf::from(path);
    }

    let bundled = app_dir.join("src-tauri/binaries/frankenphp");
    if bundled.exists() {
        return bundled;
    }

    PathBuf::from("frankenphp")
}

fn detect_ollama() -> bool {
    TcpStream::connect_timeout(
        &"127.0.0.1:11434".parse().expect("valid ollama address"),
        Duration::from_millis(350),
    )
    .is_ok()
}
