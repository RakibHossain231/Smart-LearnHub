<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Lessons</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: #f4f6f8;
      color: #1f2937;
    }

    .navbar {
      background: #ffffff;
      border-bottom: 1px solid #e5e7eb;
      padding: 16px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 22px;
      font-weight: 700;
      color: #111827;
    }

    .nav-links {
      display: flex;
      gap: 24px;
    }

    .nav-links a {
      text-decoration: none;
      color: #6b7280;
      font-size: 14px;
    }

    .student {
      font-size: 14px;
      font-weight: 600;
    }

    .page-header {
      max-width: 1200px;
      margin: 30px auto 20px;
      padding: 0 20px;
    }

    .page-header h1 {
      font-size: 30px;
      margin-bottom: 8px;
      color: #111827;
    }

    .page-header p {
      color: #6b7280;
      font-size: 14px;
    }

    .layout {
      max-width: 1200px;
      margin: 0 auto 40px;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 24px;
    }

    .sidebar {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 20px;
      height: fit-content;
    }

    .sidebar h3 {
      font-size: 18px;
      margin-bottom: 16px;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar li {
      padding: 12px 14px;
      border-radius: 10px;
      font-size: 14px;
      color: #374151;
      margin-bottom: 8px;
      background: #f9fafb;
      cursor: pointer;
    }

    .sidebar li.active {
      background: #dbeafe;
      color: #1d4ed8;
      font-weight: 700;
    }

    .content {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .top-box {
      background: linear-gradient(135deg, #0f172a, #1e3a8a);
      color: white;
      border-radius: 18px;
      padding: 26px;
    }

    .top-box h2 {
      font-size: 24px;
      margin-bottom: 8px;
    }

    .top-box p {
      font-size: 14px;
      color: #dbeafe;
      line-height: 1.6;
    }

    .section-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .section-sub {
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 16px;
    }

    .lesson-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    .lesson-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      overflow: hidden;
      transition: 0.2s ease;
    }

    .lesson-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    }

    .lesson-thumb {
      height: 140px;
      background: linear-gradient(135deg, #2563eb, #1e40af);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 28px;
      font-weight: 700;
    }

    .pdf-thumb {
      background: linear-gradient(135deg, #10b981, #047857);
    }

    .lesson-body {
      padding: 16px;
    }

    .lesson-tag {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      padding: 5px 10px;
      border-radius: 999px;
      margin-bottom: 10px;
      background: #eff6ff;
      color: #1d4ed8;
    }

    .lesson-tag.pdf {
      background: #ecfdf5;
      color: #047857;
    }

    .lesson-body h4 {
      font-size: 17px;
      margin-bottom: 8px;
      color: #111827;
      line-height: 1.4;
    }

    .lesson-body p {
      font-size: 13px;
      color: #6b7280;
      line-height: 1.6;
      margin-bottom: 14px;
    }

    .lesson-meta {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      color: #6b7280;
      margin-bottom: 14px;
    }

    .lesson-actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      flex: 1;
      text-align: center;
      text-decoration: none;
      padding: 10px 12px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 700;
      border: 1px solid #d1d5db;
      color: #111827;
      background: #fff;
    }

    .btn-primary {
      background: #111827;
      color: #ffffff;
      border-color: #111827;
    }

    .footer {
      background: #06122d;
      color: #fff;
      margin-top: 50px;
      padding: 30px 20px 12px;
    }

    .footer-grid {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1fr;
      gap: 30px;
    }

    .footer h4 {
      margin-bottom: 10px;
      font-size: 15px;
    }

    .footer p,
    .footer a {
      display: block;
      color: #cbd5e1;
      text-decoration: none;
      font-size: 13px;
      line-height: 1.9;
    }

    .footer-brand {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .footer-bottom {
      max-width: 1200px;
      margin: 18px auto 0;
      border-top: 1px solid rgba(255,255,255,0.08);
      padding-top: 12px;
      text-align: center;
      color: #94a3b8;
      font-size: 12px;
    }

    @media (max-width: 1000px) {
      .lesson-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 650px) {
      .lesson-grid {
        grid-template-columns: 1fr;
      }

      .navbar {
        flex-direction: column;
        gap: 12px;
      }

      .footer-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <?php include 'student_navbar.php'; ?>
  
    <div class="student">John Student</div>
  </header>

  <div class="page-header">
    <h1>Course Lessons</h1>
    <p>Watch videos and open lesson PDFs from one clean student page.</p>
  </div>

  <main class="layout">
    <aside class="sidebar">
      <h3>Course Modules</h3>
      <ul>
        <li class="active">Introduction</li>
        <li>HTML Basics</li>
        <li>CSS Styling</li>
        <li>JavaScript</li>
        <li>Projects</li>
        <li>Resources</li>
      </ul>
    </aside>

    <section class="content">
      <div class="top-box">
        <h2>Complete Web Development Bootcamp</h2>
        <p>
          This student lesson page is designed as a card layout. Students can
          quickly open videos and PDFs without a large video screen at the top.
        </p>
      </div>

      <div>
        <div class="section-title">Lesson Content</div>
        <div class="section-sub">Videos and PDF materials are shown in rows.</div>

        <div class="lesson-grid">
          <div class="lesson-card">
            <div class="lesson-thumb">▶</div>
            <div class="lesson-body">
              <span class="lesson-tag">VIDEO</span>
              <h4>Introduction to Web Development</h4>
              <p>Start with the basics of how websites work and how front-end development begins.</p>
              <div class="lesson-meta">
                <span>12 min</span>
                <span>Lesson 1</span>
              </div>
              <div class="lesson-actions">
                <a href="#" class="btn btn-primary">Watch</a>
                <a href="#" class="btn">Details</a>
              </div>
            </div>
          </div>

          <div class="lesson-card">
            <div class="lesson-thumb">▶</div>
            <div class="lesson-body">
              <span class="lesson-tag">VIDEO</span>
              <h4>HTML Document Structure</h4>
              <p>Learn headings, paragraphs, links, images, and the structure of an HTML page.</p>
              <div class="lesson-meta">
                <span>18 min</span>
                <span>Lesson 2</span>
              </div>
              <div class="lesson-actions">
                <a href="#" class="btn btn-primary">Watch</a>
                <a href="#" class="btn">Details</a>
              </div>
            </div>
          </div>

          <div class="lesson-card">
            <div class="lesson-thumb pdf-thumb">PDF</div>
            <div class="lesson-body">
              <span class="lesson-tag pdf">PDF</span>
              <h4>HTML Quick Revision Notes</h4>
              <p>Download or read the summary notes for HTML tags and structure.</p>
              <div class="lesson-meta">
                <span>8 pages</span>
                <span>Resource</span>
              </div>
              <div class="lesson-actions">
                <a href="#" class="btn btn-primary">Open</a>
                <a href="#" class="btn">Download</a>
              </div>
            </div>
          </div>

          <div class="lesson-card">
            <div class="lesson-thumb">▶</div>
            <div class="lesson-body">
              <span class="lesson-tag">VIDEO</span>
              <h4>CSS Colors and Fonts</h4>
              <p>Understand how to style text, backgrounds, and improve page design using CSS.</p>
              <div class="lesson-meta">
                <span>14 min</span>
                <span>Lesson 3</span>
              </div>
              <div class="lesson-actions">
                <a href="#" class="btn btn-primary">Watch</a>
                <a href="#" class="btn">Details</a>
              </div>
            </div>
          </div>

          <div class="lesson-card">
            <div class="lesson-thumb">▶</div>
            <div class="lesson-body">
              <span class="lesson-tag">VIDEO</span>
              <h4>CSS Box Model</h4>
              <p>Learn margin, padding, border, and width concepts with simple examples.</p>
              <div class="lesson-meta">
                <span>16 min</span>
                <span>Lesson 4</span>
              </div>
              <div class="lesson-actions">
                <a href="#" class="btn btn-primary">Watch</a>
                <a href="#" class="btn">Details</a>
              </div>
            </div>
          </div>

          <div class="lesson-card">
            <div class="lesson-thumb pdf-thumb">PDF</div>
            <div class="lesson-body">
              <span class="lesson-tag pdf">PDF</span>
              <h4>CSS Practice Sheet</h4>
              <p>Use this worksheet to practice selectors, colors, spacing, and layout tasks.</p>
              <div class="lesson-meta">
                <span>12 pages</span>
                <span>Worksheet</span>
              </div>
              <div class="lesson-actions">
                <a href="#" class="btn btn-primary">Open</a>
                <a href="#" class="btn">Download</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">LearnHub</div>
        <p>Empowering learners worldwide with quality education and professional development.</p>
      </div>

      <div>
        <h4>Platform</h4>
        <a href="#">About Us</a>
        <a href="#">Courses</a>
        <a href="#">Blog</a>
        <a href="#">Contact</a>
      </div>

      <div>
        <h4>Resources</h4>
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Cookie Policy</a>
      </div>

      <div>
        <h4>Community</h4>
        <a href="#">Student Forum</a>
        <a href="#">Teach on LearnHub</a>
        <a href="#">Become a Partner</a>
        <a href="#">Affiliate Program</a>
      </div>
    </div>

    <div class="footer-bottom">
      © 2026 LearnHub. All rights reserved.
    </div>
  </footer>
</body>
</html>