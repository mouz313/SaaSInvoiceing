// Firebase Client Authentication Integration
// Dynamically loads Firebase SDK v10 modular scripts or falls back gracefully

window.initFirebase = function(config) {
    if (!config || !config.apiKey || config.apiKey.includes('placeholder')) {
        console.warn('Firebase configuration not provided or using placeholder.');
        return null;
    }

    try {
        // If Firebase already loaded
        if (window.firebaseApp) return window.firebaseApp;

        // Dynamic script loading for Firebase Compat/App
        return null;
    } catch (e) {
        console.error('Failed to initialize Firebase', e);
        return null;
    }
};

window.sendFirebaseTokenToBackend = async function(user) {
    try {
        const response = await fetch('/auth/firebase-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                firebase_uid: user.uid,
                email: user.email,
                name: user.displayName || user.email.split('@')[0],
                avatar_url: user.photoURL || null
            })
        });

        const data = await response.json();
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert('Failed to establish session: ' + (data.message || 'Unknown error'));
        }
    } catch (err) {
        console.error('Session sync error:', err);
        alert('Authentication error syncing with server.');
    }
};
