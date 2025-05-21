# 🏨 Room Reserve — Laravel 12 Demo Application

**Room Reserve** is a demo Laravel 12 application that showcases AI-assisted, prompt-driven test-first development. It demonstrates how to automatically generate test scenarios and implement logic that conforms to modern Laravel and PHP best practices.

---

## ✨ Project Purpose

This application demonstrates:

- Generating **feature test scenarios** using AI-generated prompts.
- Implementing **application logic** to pass those tests using TDD.
- Applying **Laravel 12 best practices** including modern validation, routing, and service structures.

---

## 🧪 Feature Test Prompt Format

To generate feature tests, we use the following structured prompt:

```aiignore
You are a Laravel 12 developer writing feature test scenarios for: {feature}.

Use the method name format: testReturns[StatusText]When [Condition]

Each scenario should include:
- The expected HTTP status code (e.g., 200, 404, 201)
- Whether the database is used or changed
- Make use of factory for models
- Relevant inputs (route, request data)
- Expected response structure if applicable

Output just the scenario names + a brief explanation of what each test does in text docs for php.
```

**Example Output:**
```php
/**
 * testReturns201WhenReservationIsSuccessfullyCreated
 *
 * Tests that a room reservation is created successfully and returns a 201 status.
 * Uses factories to create a user and room.
 * POST /api/reservations with valid data.
 * Asserts database has new reservation record.
 * Expects response to include reservation ID and timestamps.
 */
```

---

## 🔧 Application Logic Prompt

The application logic is written test-first based on these guidelines:

```
You are a Laravel 12 developer working test-first.

Your task is to implement the application logic required to pass the existing feature tests.

Follow PHP 8.2+ and modern Laravel best practices:
- Use route model binding where applicable
- Apply form request validation classes
- Return proper HTTP status codes and JSON structures
- Use Eloquent relationships and DB logic
- Prevent logical conflicts (e.g. overlapping reservations)
- Use service classes or policies when appropriate
- Keep controllers focused (single responsibility)
- Handle errors clearly and consistently

Start by implementing the necessary controller methods. Output only the relevant code.
```

---

## 🧱 Tech Stack

- **Laravel 12 (PHP 8.2+)**
- PHPUnit / Laravel Feature Tests
- Eloquent ORM
- Route Model Binding
- Form Request Validation
- Service Class Architecture

---

## 🚀 Getting Started

```bash
git clone https://github.com/your-org/room-reserve.git
cd room-reserve
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

To run tests:

```bash
php artisan test
```


---

## 🧠 Prompt-Driven Workflow Summary

| Step | Action |
|------|--------|
| 1️⃣   | Generate feature tests using structured prompt |
| 2️⃣   | Implement controller logic to pass tests        |
| 3️⃣   | Apply Laravel 12 & PHP 8.2 best practices       |
| 4️⃣   | Repeat per feature                              |

---

## 📽️ Presentation

Want a visual walkthrough? Check out the project presentation:

👉 [View Presentation Slides](https://fhholding.sharepoint.com/:p:/s/MTTechnology/EQri_IORFVNIjpvkDlZWMqMBrH1XZSjdczysWjBYX4HJvA?e=0jHZrO)

---

## 💡 Example Features

- Room creation and management
- Room reservation with date validation
- Cancellation and conflict prevention
- Room availability listing

---

## 📄 License

This project is licensed under the **MIT License**. Use freely with attribution.
