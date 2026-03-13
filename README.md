# task_manager_application

This is a full-featured, web-based application covering many aspects of modern PHP developement, including database relationship, 
real time updates, API developemet, and front end integration.


Frontend Repo: https://github.com/adiakijyoti/task_manager_application/tree/main/frontend
Backend Repo: https://github.com/adiakijyoti/task_manager_application/tree/main/backend

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Database Design](#database-design)
- [Installation](#installation)
- [Deployment](#deployment)
- [Future Improvements](#future-improvements)
- [Credits](#credits)


---

## Overview

The detailed project outline provides a comprehensive experience covering many aspects of modern PHP development, 
including database relationships, real time updates, API development, and frontend integration. A web based task 
management application to organise, track and collaborate on tasks.

### Motivation
 It is  one of the most strategically valuable full‑stack projects you can build. It sits at the intersection of 
 real‑world utility, technical depth, and architectural sophistication.


### Objective
A web based task management application that allow users to organise, track and collaborate on tasks with projects or board,
similar to simplified version of Trello or Asana.

### Learning Outcomes
- Built full authentication system
- Designed RESTful API
- Implemented CRUD operations
- Connected frontend to backend
- Deployed full-stack application

---

## Features

- User Authentication (Register/Login/Logout)
- Role-based access control
- Full CRUD functionality
- Dashboard with analytics
- Fully responsive design
  

---

## Tech Stack

### Frontend
- React / Next.js / Vue
- HTML5
- CSS3 / Tailwind
- Axios / Fetch API

### Backend
- xampp
- Node.js and npm
- Composer


### Database
- PHPMyadmin

### Tools
- Git & GitHub
- VS Code


---

## Architecture

Client (Frontend)  
↓  
Server (REST API)  
↓  
Database  

Folder Structure Example:

```
client/
server/
  ├── controllers/
  |          └──controls.js
  ├── routes/
  ├── models/
  ├── middleware/
  └── config/
```

---

## Database Design

### Users
- id
- name
- email
- password
- role
- created_at

### Items / Tasks / Products
- id
- title
- description
- status
- user_id (Foreign Key)

---

## Installation

### Clone the Repository

```bash
git clone https://github.com/adiakijyoti/task_manager_application.git
cd task_manager_application
```

### Install Dependencies

Frontend:

```bash
cd client
npm install
```

Backend:

```bash
cd server
npm install
```

### Run Development Servers

Backend:

```bash
npm run dev
```

Frontend:

```bash
npm start
```



## Deployment

- Backend deployed on Render / Railway / AWS
- Frontend deployed on Vercel / Netlify
- Database hosted on MongoDB Atlas / Supabase

---

## Future Improvements

- Add email verification
- Add unit & integration testing
- Add real-time notifications
- Improve UI animations
- Add admin dashboard

---

## Credits

Developer: Jyoti Sharma 
GitHub: https://github.com/adiakijyoti  

---
