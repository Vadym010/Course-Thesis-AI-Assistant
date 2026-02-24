📘 Course Thesis AI Assistant

AI-powered web application for structured academic research and thesis writing support.
Built with Yii2 (PHP) and designed to integrate AI-based content extraction, filtering, and analysis into the academic workflow.

🚀 Project Overview

This project is an AI-assisted research tool that helps students:

Extract key fragments from scientific articles

Store structured academic quotes in a database

Filter and evaluate relevance (Tinder-like interface)

Organize materials by thesis sections

Switch between original text (EN) and translation (UA)

Analyze personal research decisions

The main goal is to optimize the academic writing process using structured data and AI extraction.

🎯 Problem It Solves

Traditional thesis writing workflow:

Search for articles

Read entire papers

Copy useful fragments manually

Organize content in Word

Lose structure and traceability

This project introduces:

Structured JSON-based AI extraction

Database-driven quote storage

Decision-based filtering system

Clean UI for research review

🧠 Core Idea

Instead of copying random text into Word, the system:

Uses AI to extract:

Definition

Etiology

Risk factors

Pathogenesis

Symptoms

Epidemiology

Stores:

Exact quotes (verbatim)

Ukrainian translation

Section reference

Topic classification

Allows users to evaluate each quote:

✅ Useful

❌ Not relevant

⏳ Later

Provides structured research review interface.

🏗 Architecture
Backend

PHP 7.x

Yii2 Framework

MySQL

ActiveRecord ORM

Core Entities

sources — scientific articles

quotes — extracted text fragments

votes — user decisions (like/dislike/later)

Relations

One Source → Many Quotes

One User → Many Votes

One Quote → Many Votes

📱 UI Features
🔹 Swipe Interface (Mobile-first)

Full-screen card layout

Scrollable text

Round action buttons

Keyboard shortcuts (desktop)

🔹 Decision Tabs

View by:

Useful

Not relevant

Later

Real-time counters

🔹 Language Toggle (JS-powered)

Instant switch between:

English (original)

Ukrainian (translation)

No page reload

📊 Research Control

Each quote contains:

Exact academic citation (verbatim)

Translation

Topic category

Source reference

This ensures:

Academic traceability

Easy Ctrl+F validation

Structured research logic

💡 Why This Project Matters (AI Perspective)

This is not just CRUD.

It demonstrates:

AI prompt engineering

Structured data extraction

Human-in-the-loop validation system

Decision-based knowledge filtering

Academic workflow automation

The system bridges:

LLM output → Structured storage → Human evaluation → Organized research material

🛠 Example Workflow

Paste scientific article link

AI extracts structured JSON

Import JSON into system

Review quotes via swipe UI

Filter by relevance

Build structured thesis section

📌 Future Improvements

Automatic draft generation from approved quotes

AI-powered section summarization

Anti-plagiarism similarity analysis

Collaborative research mode

Embedding-based semantic search

RAG integration

🎓 Motivation

This project was built while writing a university thesis.
It reflects a practical need for structured AI-assisted academic research tools.

📎 Tech Stack

PHP

Yii2

MySQL

JavaScript (vanilla)

HTML/CSS (mobile-first design)
