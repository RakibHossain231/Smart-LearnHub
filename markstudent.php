<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mark Submissions</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: #f6f6f6;
      color: #111827;
    }

    .page-title {
      color: #cfcfcf;
      font-size: 32px;
      font-weight: 700;
      margin: 0;
      padding: 8px 0 6px 2px;
    }

    .navbar {
      background: #fff;
      border-bottom: 1px solid #d9d9d9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 18px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 700;
      font-size: 20px;
      color: #111827;
    }

    .logo-icon {
      color: #2563eb;
      font-size: 16px;
    }

    .nav-links {
      display: flex;
      gap: 28px;
      align-items: center;
    }

    .nav-links a {
      text-decoration: none;
      font-size: 11px;
      color: #6b7280;
    }

    .user-box {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      color: #111827;
    }

    .container {
      max-width: 1180px;
      margin: 0 auto;
      padding: 18px 14px 0;
      min-height: calc(100vh - 220px);
    }

    .back-link {
      display: inline-block;
      text-decoration: none;
      color: #6b7280;
      font-size: 11px;
      margin-bottom: 14px;
    }

    .heading {
      font-size: 23px;
      font-weight: 700;
      margin-bottom: 6px;
      color: #111827;
    }

    .subheading {
      font-size: 12px;
      color: #9ca3af;
      margin-bottom: 18px;
    }

    .content-grid {
      display: grid;
      grid-template-columns: 2.1fr 1fr;
      gap: 18px;
      align-items: start;
    }

    .card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 16px;
    }

    .card-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 16px;
      color: #111827;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      font-size: 11px;
      color: #111827;
      font-weight: 700;
      padding: 0 8px 12px 8px;
    }

    td {
      font-size: 11px;
      color: #374151;
      padding: 12px 8px;
      border-top: 1px solid #f1f5f9;
      vertical-align: middle;
    }

    .type-cell {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .doc-icon {
      color: #6b7280;
      font-size: 12px;
    }

    .grade {
      font-weight: 700;
      color: #111827;
      font-size: 16px;
    }

    .muted {
      color: #9ca3af;
      font-size: 10px;
    }

    .status-pill {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 9px;
      font-weight: 600;
      white-space: nowrap;
    }

    .submitted {
      background: #dcfce7;
      color: #16a34a;
    }

    .review {
      background: #fef3c7;
      color: #d97706;
    }

    .notsubmitted {
      background: #f3f4f6;
      color: #6b7280;
    }

    .excellent {
      color: #22c55e;
      font-size: 10px;
      font-weight: 600;
    }

    .good {
      color: #3b82f6;
      font-size: 10px;
      font-weight: 600;
    }

    .notgraded {
      color: #9ca3af;
      font-size: 10px;
    }

    .date-cell {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #6b7280;
      font-size: 10px;
    }

    .summary-top {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 18px;
    }

    .avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: #2563eb;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
    }

    .student-name {
      font-size: 12px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 2px;
    }

    .student-id {
      font-size: 10px;
      color: #9ca3af;
    }

    .divider {
      border-top: 1px solid #f1f5f9;
      margin: 12px 0 16px;
    }

    .overall-label {
      font-size: 10px;
      color: #9ca3af;
      margin-bottom: 8px;
    }

    .overall-score {
      font-size: 18px;
      font-weight: 700;
      color: #16a34a;
      margin-bottom: 14px;
    }

    .overall-score span {
      font-size: 44px;
      line-height: 1;
    }

    .stats-list {
      margin-bottom: 18px;
    }

    .stats-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 10px;
      color: #6b7280;
      margin-bottom: 10px;
    }

    .stats-row strong {
      color: #111827;
      font-size: 10px;
    }

    .trend-box {
      margin-top: 10px;
      border: 1px solid #f1f5f9;
      background: #fafafa;
      border-radius: 8px;
      padding: 12px;
    }

    .trend-title {
      font-size: 9px;
      color: #9ca3af;
      margin-bottom: 8px;
    }

    .trend-text {
      font-size: 10px;
      color: #111827;
      font-weight: 600;
      display: flex;
      align-items: flex-start;
      gap: 6px;
      line-height: 1.4;
    }

    .trend-arrow {
      color: #22c55e;
      font-size: 12px;
      margin-top: 1px;
    }

    .footer {
      background: #06122d;
      color: #fff;
      margin-top: 28px;
      padding: 26px 18px 10px;
    }

    .footer-inner {
      max-width: 1180px;
      margin: 0 auto;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: 1.4fr 1fr 1fr 1fr;
      gap: 36px;
      margin-bottom: 14px;
    }

    .footer-brand {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .footer h4 {
      font-size: 13px;
      margin-bottom: 10px;
      color: #fff;
    }

    .footer p,
    .footer a {
      color: #b8c1d1;
      text-decoration: none;
      font-size: 11px;
      line-height: 1.9;
      display: block;
    }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      text-align: center;
      padding-top: 8px;
      color: #b8c1d1;
      font-size: 10px;
    }

    @media (max-width: 900px) {
      .content-grid {
        grid-template-columns: 1fr;
      }

      .footer-grid {
        grid-template-columns: 1fr 1fr;
      }

      .navbar {
        flex-direction: column;
        gap: 10px;
      }
    }

    @media (max-width: 600px) {
      .footer-grid {
        grid-template-columns: 1fr;
      }

      .page-title {
        font-size: 24px;
      }

      .heading {
        font-size: 20px;
      }

      .card {
        overflow-x: auto;
      }

      table {
        min-width: 650px;
      }
    }
  </style>
</head>
<body>
  <div class="page-title">Mark Submissions</div>

  <header class="navbar">
    <div class="logo">
      <span class="logo-icon">🎓</span>
      <span>LearnHub</span>
    </div>

    <nav class="nav-links">
      <a href="#">Home</a>
      <a href="#">Courses</a>
      <a href="#">Dashboard</a>
    </nav>

    <div class="user-box">
      <span>John Student</span>
      <span>⌄</span>
    </div>
  </header>

  <main class="container">
    <a href="#" class="back-link">← Back to Dashboard</a>

    <h1 class="heading">Complete Web Development Bootcamp</h1>
    <p class="subheading">Your grades and performance history</p>

    <div class="content-grid">
      <div class="card">
        <div class="card-title">Grade Breakdown</div>

        <table>
          <thead>
            <tr>
              <th>Assessment Type</th>
              <th>Grade</th>
              <th>Status</th>
              <th>Performance</th>
              <th>Submitted</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="type-cell">
                <span class="doc-icon">📄</span>
                <span>Assignment</span>
              </td>
              <td class="grade">92%</td>
              <td><span class="status-pill submitted">Graded</span></td>
              <td><span class="excellent">Excellent</span></td>
              <td class="date-cell"><span>🗓</span><span>2026-03-15</span></td>
            </tr>

            <tr>
              <td class="type-cell">
                <span class="doc-icon">📄</span>
                <span>Quiz</span>
              </td>
              <td class="grade">88%</td>
              <td><span class="status-pill submitted">Graded</span></td>
              <td><span class="good">Good</span></td>
              <td class="date-cell"><span>🗓</span><span>2026-03-10</span></td>
            </tr>

            <tr>
              <td class="type-cell">
                <span class="doc-icon">📄</span>
                <span>Midterm</span>
              </td>
              <td class="muted">-</td>
              <td><span class="status-pill review">Pending Review</span></td>
              <td><span class="notgraded">Not Graded</span></td>
              <td class="muted">-</td>
            </tr>

            <tr>
              <td class="type-cell">
                <span class="doc-icon">📄</span>
                <span>Final</span>
              </td>
              <td class="muted">-</td>
              <td><span class="status-pill notsubmitted">Not Submitted</span></td>
              <td><span class="notgraded">Not Graded</span></td>
              <td class="muted">-</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card">
        <div class="card-title">Performance Summary</div>

        <div class="summary-top">
          <div class="avatar">JS</div>
          <div>
            <div class="student-name">John Student</div>
            <div class="student-id">STU001</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="overall-label">Overall Average</div>
        <div class="overall-score"><span>90</span> %</div>

        <div class="stats-list">
          <div class="stats-row">
            <span>Graded Items</span>
            <strong>2 / 4</strong>
          </div>
          <div class="stats-row">
            <span>Pending Review</span>
            <strong>1</strong>
          </div>
          <div class="stats-row">
            <span>Not Submitted</span>
            <strong>1</strong>
          </div>
        </div>

        <div class="divider"></div>

        <div class="trend-box">
          <div class="trend-title">Performance Trend</div>
          <div class="trend-text">
            <span class="trend-arrow">↗</span>
            <span>Keep up the good work!</span>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-grid">
        <div>
          <div class="footer-brand">🎓 LearnHub</div>
          <p>Empowering learners worldwide with quality education and professional development.</p>
        </div>

        <div>
          <h4>Platform</h4>
          <a href="#">About Us</a>
          <a href="#">Careers</a>
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
          <a href="#">Become A Partner</a>
          <a href="#">Affiliate Program</a>
        </div>
      </div>

      <div class="footer-bottom">
        © 2026 LearnHub. All rights reserved.
      </div>
    </div>
  </footer>
</body>
</html>