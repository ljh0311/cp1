module.exports = {
    php: "C:/Users/user/Documents/SITstuffs/php/php.exe",  // Using forward slashes for Windows path
    // Additional configurations can be added here
    host: "localhost",
    port: 8000,
    root: "./",
    open: false,
    injectBody: false, // Prevent Five Server from injecting its client script
    highlight: false,  // Disable highlighting as it can interfere with PHP output
    debug: true,  // Enable debugging
    phpIni: {
        "display_errors": "On",
        "error_reporting": "E_ALL",
        "log_errors": "On",
        "error_log": "php_errors.log"
    }
} 