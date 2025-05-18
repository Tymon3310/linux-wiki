// https://github.com/g-otn/bad-apple-browser-console
// Modified to be manually triggered from the console without a button
import { doTimer } from './doTimer.js';
const isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;

const fps = 30,
    msPerFrame = 1000 / fps;
let frames;
let framesReady = false;
// window.stopTimer will be used by doTimer.js

// Export functions for use as a module
export function stopBadAppleConsole() {
    window.stopTimer = true;
    console.log("Bad Apple animation stopped by user.");
};

export async function playBadAppleConsole() {
    if (!framesReady) {
        console.log("Klatki nie zostały jeszcze załadowane. Wywołaj loadBadAppleFramesConsole() lub poczekaj na automatyczne załadowanie.");
        // Optionally, try to load if not already loaded
        if (!frames) loadBadAppleFramesConsole();
        return;
    }
    if (!frames || frames.length === 0) {
        console.error("Nie ma klatek do wyświetlenia.");
        return;
    }

    console.clear();
    console.log("Starting Bad Apple in console...");
    window.stopTimer = false;

    if (typeof doTimer !== 'function') {
        console.error("Funkcja doTimer nie jest zdefiniowana. Upewnij się, że doTimer.js jest załadowany przed easterEgg.js");
        // Basic fallback if doTimer is missing (less accurate)
        let currentFrame = 0;
        function simpleFrameDisplay() {
            if (window.stopTimer || currentFrame >= frames.length) {
                console.log("Bad Apple!! - Animacja " + (window.stopTimer ? "Zatrzymana." : "Zakończona."));
                if (window.stopTimer) window.stopTimer = false; // Reset stop flag
                return;
            }
            console.clear();
            console.log(frames[currentFrame]);
            currentFrame++;
            setTimeout(simpleFrameDisplay, msPerFrame);
        }
        simpleFrameDisplay();
        return;
    }

    // Start printing directly using doTimer
    if (frames && frames.length > 0) {
        console.log(frames[0]); // Show the first frame immediately
        doTimer(frames.length * msPerFrame, fps,
            (steps, count) => { // oninstance
                if (frames[count + 1]) {
                    // Clear console every second (every 'fps' frames) instead of every frame
                    // or based on the Firefox condition
                    if (count % fps === 0) {
                        console.clear();
                    }
                    console.log(frames[count + 1]);
                }
            },
            () => { // oncomplete
                if (window.stopTimer) {
                } else {
                    console.log("Bad Apple!! - Animacja Zakończona.");
                }
                window.stopTimer = false; // Reset for next play
            }
        );
    } else {
        console.error("Nie ma klatek do wyświetlenia.");
    }
};

export function loadBadAppleFramesConsole() {
    if (framesReady) {
        console.log("Klatki już załadowane.");
        // return; // Allow re-load if desired
    }
    // console.log('Fetching Bad Apple frames...');
    framesReady = false; // Reset ready state for loading

    fetch('include/bd.txt')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(content => {
            // console.log('Processing Bad Apple frames...');
            frames = content.split('\n\n'); // Corrected: Use actual newlines for splitting
            // Delete last empty 'frame' created by split command if it's truly empty
            if (frames.length > 0 && frames[frames.length - 1].trim() === '') {
                frames.pop();
            }

            if (!frames || frames.length === 0 || !frames[0]) {
                console.error('Nie udało się załadować lub sparsować klatek. Zawartość może być pusta lub format jest niepoprawny.');
                framesReady = false;
                // Optionally, set up some default frames here as a fallback
                // frames = ["Error loading frames", ":("];
                // framesReady = true;
                return;
            }

            console.log(`Załadowano ${frames.length} klatek.`);
            // Show tip to properly display frames
            // if (frames[0]) { // Keep this logic commented out or remove if not desired
            //     const firstFrameLines = frames[0].split('\n'); 
            //     if (firstFrameLines[0]) {
            //         const helpLine =
            //             new Array(firstFrameLines[0].length + 1).join('⣿') +
            //             '⣿⣿⣿⣿⣿⣿⣿⣿'; 
            //         // console.clear(); // REMOVED: User wants to avoid clearing console here
            //         console.info(
            //             "%cTweak console width/zoom so that the following line doesn't break:",
            //             'font-size: 18pt' 
            //         ); // REMOVED: User wants to avoid this message during load
            //         console.info('%c' + helpLine + '\n', 'color: cyan; font-weight: bold;'); // REMOVED
            //     }
            // }
            framesReady = true;
            // console.log("Bad Apple frames ready. Type playBadAppleConsole() to start.");
        })
        .catch(error => {
            console.error('Błąd ładowania` bd.txt:', error);
            // Optionally, set up some default frames here as a fallback
            // frames = ["Error loading frames", ":("];
            // framesReady = true;
            // console.log("Type playBadAppleConsole() to try again after fixing the issue, or loadBadAppleFramesConsole() to reload.");
        });
};
window.playBadAppleConsole = playBadAppleConsole;
window.stopBadAppleConsole = stopBadAppleConsole;
// Automatically load frames when the script is loaded.
// loadBadAppleFramesConsole(); // Commented out: Will be called from script.js

// Reminder for the user
// console.log("Bad Apple Console Easter Egg: Type playBadAppleConsole() to play, stopBadAppleConsole() to stop."); // Commented out: script.js will handle initialization messages
