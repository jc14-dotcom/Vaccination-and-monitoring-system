/**
 * DEBUG FILE - FCM Duplicate Analysis
 * This file is for debugging purposes only and should not be loaded in production.
 * To use: Include this script in browser console when debugging FCM notification issues.
 * 
 * PRODUCTION: This file is disabled. Remove or keep for debugging reference.
 */

// DEBUG: Uncomment for FCM debugging
// console.log('=== FCM Duplicate Analysis ===');
// console.log('1. Check if multiple event listeners are registered');
// console.log('2. Check if both foreground and background handlers fire');
// console.log('3. Monitor notification creation');

// // Override Notification constructor to track calls
// const OriginalNotification = window.Notification;
// let notificationCount = 0;
// window.Notification = function(...args) {
//     notificationCount++;
//     console.log(`[TRACKING] Notification #${notificationCount} created:`, args[0], args[1]);
//     console.trace('Notification creation stack trace');
//     return new OriginalNotification(...args);
// };
// window.Notification.permission = OriginalNotification.permission;
// window.Notification.requestPermission = OriginalNotification.requestPermission.bind(OriginalNotification);

// console.log('Paste this code in browser console to track notification creation');
