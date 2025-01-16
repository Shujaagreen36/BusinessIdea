// server.js

require('dotenv').config(); // ✅ Load .env first

const express = require('express');
const multer = require('multer');
const nodemailer = require('nodemailer');
const path = require('path');
const fs = require('fs');
const compression = require('compression');

const app = express();
const PORT = 3000;

// ✅ Ensure uploads and submissions directories exist
['uploads', 'submissions'].forEach(dir => {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir);
    }
});

// ✅ File Upload Configuration
const upload = multer({
    dest: 'uploads/',
    limits: { fileSize: 5 * 1024 * 1024 }, // 5MB limit
    fileFilter: (req, file, cb) => {
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (allowedTypes.includes(file.mimetype)) {
            cb(null, true);
        } else {
            cb(new Error('❌ Invalid file type. Only PDF, DOC, and DOCX are allowed.'));
        }
    }
});

// ✅ Middleware
app.use(compression());
app.use(express.static('public'));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// ✅ Email Configuration
const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: process.env.EMAIL_USER,
        pass: process.env.EMAIL_PASS
    }
});

console.log('Email:', process.env.EMAIL_USER);
console.log('Password:', process.env.EMAIL_PASS);


// ✅ Handle Form Submission
app.post('/submit', upload.single('file'), (req, res) => {
    const { name, email, genre } = req.body;
    const file = req.file;

    if (!file) {
        return res.status(400).send('❌ File upload failed.');
    }

    // 1️⃣ Email to Admin with Attachment
    const adminMailOptions = {
        from: 'no-reply@maplewoodreview.com',
        to: 'fipressinc@gmail.com',
        subject: '📥 New Submission Received',
        text: `Name: ${name}\nEmail: ${email}\nGenre: ${genre}`,
        attachments: [{ filename: file.originalname, path: file.path }]
    };

    transporter.sendMail(adminMailOptions, (err) => {
        if (err) {
            console.error('❌ Error sending email to admin:', err);
            return res.status(500).send('❌ Failed to send email to admin.');
        }

        // 2️⃣ Confirmation Email to Participant
        const userMailOptions = {
            from: 'no-reply@maplewoodreview.com',
            to: email,
            subject: '✅ Thank You for Your Submission!',
            text: `Dear ${name},\n\nThank you for submitting your work to The Maplewood Review.\n\nBest regards,\nThe Maplewood Review Team`
        };

        transporter.sendMail(userMailOptions, (err) => {
            if (err) {
                console.error('❌ Error sending confirmation email:', err);
                return res.status(500).send('❌ Failed to send confirmation email.');
            }

            // 3️⃣ Save Submission Details
            const submission = { name, email, genre, filepath: file.path };
            fs.writeFileSync(`submissions/${Date.now()}.json`, JSON.stringify(submission));

            // ✅ Redirect after successful submission
            res.redirect('/thank-you.html');
        });
    });
});

// ✅ Start Server
app.listen(PORT, () => {
    console.log(`🚀 Server running at http://localhost:${PORT}`);
});
app.get('/submissions', (req, res) => {
    const files = fs.readdirSync('submissions');
    const submissions = files.map(file => {
        const content = fs.readFileSync(`submissions/${file}`);
        return JSON.parse(content);
    });
    res.json(submissions);
});
