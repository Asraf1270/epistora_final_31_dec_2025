<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as Writer | Epistora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --radius: 12px;
            --input-bg: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
            line-height: 1.5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .app-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        header {
            margin-bottom: 32px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 24px;
        }

        h1 {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .subtitle {
            color: var(--text-muted);
            margin: 0;
            font-size: 1rem;
        }

        section {
            margin-bottom: 32px;
        }

        section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        section h3::after {
            content: "";
            height: 1px;
            background: var(--border);
            flex-grow: 1;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--text-main);
        }

        input, textarea {
            width: 100%;
            padding: 12px 16px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--input-bg);
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 160px;
            line-height: 1.6;
        }

        .bookmark-entry {
            margin-bottom: 10px;
        }

        .add-link-btn {
            background: none;
            border: 1px dashed var(--border);
            color: var(--primary);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 10px;
            width: 100%;
            border-radius: var(--radius);
            transition: all 0.2s;
        }

        .add-link-btn:hover {
            background: #f5f3ff;
            border-color: var(--primary);
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.2s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);
        }

        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
            .app-card { padding: 24px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="app-card">
        <header>
            <h1>Writer Application</h1>
            <p class="subtitle">Join our community of thought leaders and storytellers.</p>
        </header>

        <form action="process_app.php" method="POST">
            <section>
                <h3>1. Personal Biodata</h3>
                <div class="grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required placeholder="Enter your name">
                    </div>
                    <div class="form-group">
                        <label>Father's Name</label>
                        <input type="text" name="father_name" required placeholder="Enter your Guardian Name">
                    </div>
                </div>

                <div class="grid">
                    <div class="form-group">
                        <label>Email ID</label>
                        <input type="email" name="email" value="<?= $_SESSION['user_email'] ?? '' ?>" required placeholder="Enter your Email">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required placeholder="+880 1XXX XXXXXX">
                    </div>
                </div>

                <div class="grid">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" required placeholder="City, Country">
                    </div>
                </div>
            </section>

            <section>
                <h3>2. Writing Portfolio</h3>
                <div class="form-group">
                    <label>Writing Sample (Min. 300 words)</label>
                    <textarea name="sample_text" rows="8" required placeholder="Paste an original article or sample here. Demonstrate your unique voice..."></textarea>
                </div>

                <div class="form-group">
                    <label>Experience Bookmarks</label>
                    <div id="bookmark-container">
                        <div class="bookmark-entry">
                            <input type="url" name="bookmarks[]" placeholder="https://medium.com/@username/my-article" required>
                        </div>
                    </div>
                    <button type="button" class="add-link-btn" onclick="addLink()">+ Add another portfolio link</button>
                </div>
            </section>

            <button type="submit" class="btn-submit">Submit My Application</button>
        </form>
    </div>
</div>

<script>
    function addLink() {
        const container = document.getElementById('bookmark-container');
        const div = document.createElement('div');
        div.className = 'bookmark-entry';
        div.innerHTML = `<input type="url" name="bookmarks[]" placeholder="https://another-link.com" style="margin-top:10px;">`;
        container.appendChild(div);
    }
</script>

</body>
</html>