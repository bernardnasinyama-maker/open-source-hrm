<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRBC HR Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .logo span {
            color: #f53003;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info a {
            color: white;
            text-decoration: none;
            background: #f53003;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .user-info a:hover {
            background: #d42c02;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #f53003;
        }
        
        .welcome-card h1 {
            color: #1e3c72;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .welcome-card p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .project-title {
            color: #f53003;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #1e3c72;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #1e3c72;
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }
        
        .recent-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .recent-section h3 {
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 3rem;
            padding: 1rem;
            color: #666;
            border-top: 1px solid #ddd;
        }
        
        .developer {
            color: #1e3c72;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            CRBC <span>HR PORTAL</span>
        </div>
        <div class="user-info">
            <span>Welcome, {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
            <a href="/logout">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome-card">
            <h1>Welcome to CRBC HR Portal</h1>
            <p>Kayunga-Bbaale-Galiraya Road Project (87km) Including Landing Site</p>
            <div class="project-title">Developed by Bernard Nasinyama</div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number">150+</div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-number">12</div>
                <div class="stat-label">Pending Leave</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-number">85%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number">45</div>
                <div class="stat-label">Payroll This Month</div>
            </div>
        </div>
        
        <div class="recent-section">
            <h3>Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <a href="/hr/attendances" style="background: #1e3c72; color: white; padding: 1rem; text-align: center; text-decoration: none; border-radius: 5px;">Manage Attendance</a>
                <a href="/hr/leaves" style="background: #1e3c72; color: white; padding: 1rem; text-align: center; text-decoration: none; border-radius: 5px;">Leave Requests</a>
                <a href="/hr/employees" style="background: #1e3c72; color: white; padding: 1rem; text-align: center; text-decoration: none; border-radius: 5px;">View Employees</a>
                <a href="/hr/payrolls" style="background: #1e3c72; color: white; padding: 1rem; text-align: center; text-decoration: none; border-radius: 5px;">Payroll</a>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2026 CRBC - Kayunga-Bbaale-Galiraya Road Project</p>
            <p class="developer">System developed by Bernard Nasinyama | bernardnasinyama@gmail.com | 0705318546</p>
        </div>
    </div>
</body>
</html>