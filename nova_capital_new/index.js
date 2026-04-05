const { app, BrowserWindow } = require('electron');
const path = require('path');

function createWindow() {
  // Create the browser window
  const win = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      nodeIntegration: true, // Allows use of Node.js in your HTML (optional)
      contextIsolation: false // Set to false if nodeIntegration is true
    }
  });

  // Load your HTML file
  win.loadFile('Homepage.html');
}

// When Electron is ready, create the window
app.whenReady().then(() => {
  createWindow();

  // If no windows are open, create one (macOS behavior)
  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

// Quit when all windows are closed (except on macOS)
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});