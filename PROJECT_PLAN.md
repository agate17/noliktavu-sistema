# Warehouse Management System - 3-5 Day Sprint

## Project Overview
Building a warehouse management system (STASH) with user roles, product management, order processing, and shelf organization.

---

## Day 1: Setup & Core Structure

### Both Together (2-3 hours)
- [ ] Set up GitHub repository and branches
- [ ir ] Create database structure (5 tables max)
- [ ir ] Basic PHP config file
- [ ] Simple CSS framework
- [ ] Test connection and git workflow

### Database Tables (Keep Simple)
- [ ] roles (id, role_name, permissions)
- [ ] users (id, username, password, role_id)
- [ ] products (id, name, category, price, quantity, company_id)
- [ ] orders (id, user_id, status, date)
- [ ] shelves (id, location, capacity)

---

## Day 2-3: Parallel Development

### Person A: Users & Dashboard
**Day 2:**
- [ ] Login/logout system (simple)   Daniels
- [ ] User management page (assign roles to users)    #AGATE
- [ ] Role management system (admin can create/edit roles)  #AGATE
- [ ] Basic dashboard with stats   Daniels   

**Day 3:**
- [ ] Shelf management interface
- [ ] Product placement on shelves
- [ ] User role permissions enforcement

**Files:** `login.php`, `users.php`, `roles.php`, `dashboard.php`, `shelves.php`

### Person B: Products & Orders
**Day 2:**
- [ ] Products CRUD (add/edit/delete/view)   Daniels
- [ ] Product categories
- [ ] Products table (matches your interface) Daniels

**Day 3:**
- [ ] Order management system  (varu jebkuru izvelies sev)
- [ ] Basic reports generation
- [ ] Order-product linking

**Files:** `products.php`, `orders.php`, `reports.php`

---

## Day 4: Integration & Polish

### Morning (Together)
- [ ] Connect user roles to features
- [ ] Link products to shelves
- [ ] Test all workflows

### Afternoon (Split)
- [ ] **Person A:** Polish UI, fix navigation  Daniels
- [ ] **Person B:** Complete reports, final testing

---

## Day 5: Final Testing (If Needed)
- [ ] Test all 3 user roles
- [ ] Fix any bugs      Daniels
- [ ] Clean up code
- [ ] Submit project

---

## Core Features (Minimum Viable)

### Must Have
- [ ] Login with 3 user roles
- [ ] Product management (add/edit/delete)
- [ ] Basic order system
- [ ] Simple shelf placement
- [ ] One report type

### Nice to Have (If Time)
- [ ] Advanced reports
- [ ] Better UI styling
- [ ] Search functionality
- [ ] Export features

---

## Daily Checklist

### End of Each Day
- [ ] Commit and push changes
- [ ] Update partner on progress
- [ ] Test your features work
- [ ] Plan next day tasks

### Key Files Structure
```
/warehouse-system
├── index.php (login)
├── dashboard.php
├── products.php
├── users.php
├── roles.php
├── orders.php
├── shelves.php
├── reports.php
├── /css (basic styles)
├── /php (config, functions)
└── /database (setup.sql)
```

---

## Success Criteria (Realistic)
- [ ] 3 user roles working
- [ ] Products CRUD complete
- [ ] Basic order processing
- [ ] Simple shelf system
- [ ] One working report
- [ ] Clean, functional interface

**Focus:** Get it working first, make it pretty second!
