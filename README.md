🎓 Graduation RSVP Portal — Interactive Guestlist Experience
A sleek, intelligent, and animated RSVP portal designed for a once-in-a-lifetime event—your graduation. This isn’t just a form submission page; it’s a cinematic experience for your guests.

💡 What It Is
An interactive RSVP system with dynamic guest logging, real-time plus-one validation, anti-duplicate protection, live guestlist typing animation with sound effects, and much more—all built with PHP, HTML, JS, and Brevo (formerly Sendinblue) email API.

🛠️ Core Features
✅ RSVP with Validation

Guests confirm attendance via a stylish interface.

Validates both name and IP to prevent duplicate entries.

➕ Plus One Submission

Limited to 3 per IP.

Prevents duplicate names (even sneaky variants).

Dynamically checks if a plus one was already added or RSVP’d.

🚫 Duplicate Protection

Blocks multiple submissions from same IP or name.

Sends a log and saves failed attempts for auditing.

🔔 Email Notifications

Only successful entries trigger email alerts to host.

All other logs are silently recorded.

🧾 Guest List Viewer

Guest names type out letter-by-letter with mechanical keystroke sounds.

Characters "fly" into their correct positions, sorted by name length.

Plus ones and main RSVP groups are separated and clearly labeled.

Mobile-optimized with smooth scrolling for long lists.

⏳ Hidden Admin Access

The "hourglass" link to view the guest list only appears to the first IP in the log — a secret admin touch.

📦 Export to CSV

One-click export of the full guest list.

🎨 Animations + Sound

Custom entrance effects, randomized sound pitch, and futuristic UI.

Typewriter sound (via mech.mp3) accompanies each character rendered on screen.

🚀 Tech Stack
Frontend: HTML, CSS, JavaScript (Vanilla)

Backend: PHP

Email Service: Brevo SMTP API

Logging: Custom .txt logs for IPs, RSVP data, debug info

Deployment: Lightweight, static + PHP compatible

👁‍🗨 Why?
To turn a boring RSVP form into an experience guests won’t forget—because even the invite should feel like part of the celebration.

