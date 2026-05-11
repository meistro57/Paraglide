use std::path::Path;
use std::sync::Mutex;

use crate::sidecar::{launch_frankenphp, SidecarError, SidecarHandle, SidecarLaunch};

pub struct LifecycleState {
    frankenphp: Mutex<Option<SidecarHandle>>,
}

impl LifecycleState {
    pub fn new() -> Self {
        Self {
            frankenphp: Mutex::new(None),
        }
    }

    pub fn start(&self, app_dir: &Path) -> Result<SidecarLaunch, SidecarError> {
        let (launch, handle) = launch_frankenphp(app_dir)?;

        let mut lock = self
            .frankenphp
            .lock()
            .expect("frankenphp mutex poisoned");
        *lock = Some(handle);

        Ok(launch)
    }

    pub fn shutdown(&self) {
        let mut lock = self
            .frankenphp
            .lock()
            .expect("frankenphp mutex poisoned");

        if let Some(handle) = lock.take() {
            let _ = handle.stop();
        }
    }
}
