# SiteHRM User Manual
### CRBC Uganda Ltd — Kayunga-Bbaale-Galiraya Road

---

## Accessing the System

| Panel | URL | Who Uses It |
|-------|-----|-------------|
| Admin Panel | `/admin` | You (Bernard) + Mr Shi |
| HR Portal | `/hr` | Thembo + All Staff |

**Remote access:** Replace `localhost:8000` with the ngrok URL from `START_REMOTE_ACCESS.bat`

---

## Login Credentials

| Name | Email | Password | Role |
|------|-------|----------|------|
| System Admin (Bernard) | einsteinbernard3000@gmail.com | ben123# | Super Admin |
| Mr Shi | mr.shi@crbc.com | shi123@ | Admin |
| Thembo Amoni | thembo.amoni@crbc.com | thembo123 | HR Assistant |
| All other staff | their@email.com | crbc2026 | Employee |

---

## Daily Operations

### 1. Recording Attendance
**Path:** Admin → HR Management → Attendances  
**Who:** Thembo (HR Assistant)

- Go to Attendances → Create
- Select employee, enter date, clock-in and clock-out times
- System automatically marks **LATE** if arrival is after 08:30
- For bulk entry: use the attendance list and add records per employee

### 2. Leave Requests
**Path:** HR Portal → Leave Requests  
**Who:** Any employee can apply; Thembo or Mr Shi approves

- Employee clicks **New Leave Request**
- Fills in leave type, start date, end date, reason
- HR Assistant sees pending requests on dashboard
- Click **Approve** or **Reject** — employee gets email notification

### 3. Processing Payroll
**Path:** Admin → HR Management → Payrolls → New  
**Who:** Mr Shi or Bernard only

1. Select employee
2. Enter gross pay
3. System **auto-calculates** PAYE, NSSF (5% employee, 10% employer), LST
4. Add allowances (transport, housing) if any
5. Add deductions (loan recovery) if any
6. Net pay is calculated automatically
7. Set status to **Completed** → employee gets payslip email

### 4. Generating Payslips
**Path:** Admin → Reports → Click **Payslip** next to any employee  
**Who:** Bernard or Mr Shi

- Opens branded CRBC payslip in new tab
- Click **Print Payslip** button (bottom right)
- Shows: earnings, PAYE, NSSF, LST, net pay, signatures

### 5. Site Expenses
**Path:** Admin → Finance → Site Expenses → New  
**Who:** Thembo creates; Mr Shi approves

- Reference number auto-generated (EXP-2026-001)
- Categories: Fuel, Airtime/Data, Per Diem, Materials, Transport, Meals, etc.
- Attach receipt photo/scan
- After submission → status shows **Pending**
- Admin approves → status changes to **Approved**
- All edits are tracked in audit trail (fraud prevention)

### 6. Correspondence Tracker
**Path:** Admin → Project → Correspondence  
**Who:** Bernard (primary), Thembo (view)

Track all official project documents:
- **RFI** — Request for Information (to consultant)
- **NCR** — Non-Conformance Report (from supervisor)
- **SI** — Site Instruction (from Resident Engineer)
- **Letter, MoM, Drawing, Variation, Payment Certificate**

Red badge on sidebar = overdue correspondence needing response

### 7. Task Board
**Path:** Admin → Work Space → Task Board  
**Who:** Bernard + Mr Shi

Kanban board with 4 columns:
- **📋 To Do** — tasks not started
- **🔄 In Progress** — ongoing tasks
- **👀 Pending Review** — waiting for approval/feedback
- **✅ Done** — completed

Drag cards between columns. Link tasks to correspondence or expenses.

### 8. Disciplinary Records
**Path:** HR Portal → Disciplinary  
**Who:** Thembo logs; Bernard reviews

- Record incidents, warnings, terminations
- All records time-stamped and audited

---

## Dashboard Widgets

### Admin Dashboard (Bernard/Mr Shi)
- Staff headcount
- Present today
- Pending leaves count
- Overdue correspondence (red if any)
- Pending expenses
- Attendance chart (7 days)
- Expense breakdown by category

### HR Portal Dashboard (Thembo)
- Same widgets above
- My pending tasks
- Recent leave requests list
- Recent expenses list
- Overdue correspondence list with due dates

---

## Starting the System Daily

### Option A — Local use only
Double-click: `START_CRBC_HRM.bat`

### Option B — Remote access (team on different devices)
Double-click: `START_REMOTE_ACCESS.bat`
- Copy the `https://xxxx.ngrok-free.app` URL
- Share with team on WhatsApp
- Add `/admin` for you and Mr Shi
- Add `/hr` for Thembo and all staff

> ⚠️ The ngrok URL changes every time you restart. Share the new URL each morning.

---

## Settings (Super Admin Only)
**Path:** Admin → System → Settings

- Configure email (Gmail SMTP for notifications)
- Clear system cache
- View system info (PHP version, DB size)

### Activating Email Notifications
1. Go to `myaccount.google.com/apppasswords`
2. Generate app password for Mail
3. In Settings page, enter:
   - SMTP Host: `smtp.gmail.com`
   - Port: `587`
   - Username: your Gmail
   - Password: the 16-character app password
4. Save — emails now send for: leave approvals, payslips, task assignments

---

## Deploying for a New Company

1. Visit `/setup` on a fresh install
2. Fill in company details, admin account
3. Click **Launch My HRM System**
4. Add employees via Admin → Employees → New

Or to wipe and reset existing system:
```bash
php artisan db:seed --class=FreshInstallSeeder --force
php artisan db:seed --class=RolePermissionSeeder --force
```

---

## Support & Development

**Developer:** Eng. Bernard Nasinyama  
**Email:** einsteinbernard3000@gmail.com  
**Project:** SGR Extension / Road Construction, Uganda

---

*SiteHRM — Built for African Construction Companies* 🇺🇬
