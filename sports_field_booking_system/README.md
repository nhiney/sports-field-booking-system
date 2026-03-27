# Secure E-commerce Shoe Shop System

A web-based e-commerce platform for purchasing shoes, built with a strong emphasis on **database security** and **secure application design**. This project demonstrates practical implementation of authentication mechanisms, access control, SQL injection prevention, and advanced login methods including face recognition and QR code authentication — all powered by Oracle Database.

> **Course**: Database Security  
> **Tech Stack**: Oracle Database, PL/SQL, ASP.NET MVC  
> **Focus**: Secure system design, database-level security enforcement, biometric authentication

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Features](#features)
3. [Tech Stack](#tech-stack)
4. [System Architecture](#system-architecture)
5. [Database Design](#database-design)
6. [Security Design](#security-design)
7. [Key Workflows](#key-workflows)
8. [Challenges and Solutions](#challenges-and-solutions)
9. [Testing](#testing)
10. [Future Improvements](#future-improvements)
11. [Conclusion](#conclusion)

---

## Project Overview

This system is a fully functional online shoe store that allows customers to browse products, manage a shopping cart, and place orders using Cash on Delivery (COD). Administrators can manage the product catalog, process orders, and control user accounts.

What distinguishes this project from a typical e-commerce application is that **security is built into every layer** — from the database schema and stored procedures up to the application's authentication and session management. The goal was to apply database security concepts in a realistic, end-to-end system rather than treating them as isolated exercises.

---

## Features

### Functional Features

- **User Registration and Login** — Account creation with secure credential storage
- **Product Catalog** — Browse, search, and filter shoes by category, brand, size, and price range
- **Shopping Cart** — Add, update, and remove items before checkout
- **Order Processing** — Place orders with COD payment; track order status
- **Admin Dashboard** — Full CRUD operations for products, orders, and user accounts
- **Role-Based Views** — Separate interfaces and permissions for Admin and Customer roles

### Security Features

- **Password Hashing** — Passwords stored using one-way cryptographic hashing (never in plaintext)
- **SQL Injection Prevention** — All database interactions use parameterized queries and stored procedures
- **Role-Based Access Control (RBAC)** — Enforced at both application and database levels
- **Stored Procedures** — Business logic encapsulated in PL/SQL to prevent direct table access
- **Database Triggers** — Automated enforcement of business rules and data integrity constraints
- **Transaction Management** — ACID-compliant order processing to prevent partial or inconsistent data
- **Face Recognition Login** — Biometric authentication as an alternative login method
- **QR Code Login** — Token-based session authentication via QR code scanning
- **Server-Side Input Validation** — All user inputs validated before processing
- **Secure Session Handling** — Session tokens managed server-side with expiration policies

---

## Tech Stack

| Layer         | Technology                                      |
|---------------|--------------------------------------------------|
| Database      | Oracle Database 19c (or Oracle XE)              |
| Backend       | ASP.NET MVC / C#                                |
| Frontend      | Razor Views, HTML, CSS, JavaScript              |
| ORM / Data    | ADO.NET with parameterized commands             |
| Procedures    | PL/SQL (Stored Procedures, Functions, Triggers) |
| Auth          | Custom authentication with hashing              |
| Biometric     | Face Recognition API integration                |
| QR Auth       | QR code generation library + session binding    |

---

## System Architecture

The application follows the **MVC (Model-View-Controller)** pattern with a clear separation between the presentation layer, business logic, and data access.

```
+------------------+       +-------------------+       +---------------------+
|                  |       |                   |       |                     |
|   View (Razor)   | <---> |   Controller (C#) | <---> |   Oracle Database   |
|   HTML/CSS/JS    |       |   Business Logic  |       |   PL/SQL Procedures |
|                  |       |   Input Validation |       |   Triggers          |
+------------------+       +-------------------+       +---------------------+
        |                          |                            |
   User Interface           Application Layer             Data Layer
   - Product pages          - Auth logic                  - Tables & Views
   - Cart UI                - RBAC enforcement            - Stored Procedures
   - Admin panels           - Session management          - Triggers & Constraints
                            - Face/QR auth handling       - Transactions
```

**Design Decisions:**

- **No raw SQL in controllers** — All data access routes through stored procedures, reducing the attack surface for SQL injection.
- **Database as security enforcer** — Business rules and access restrictions are enforced at the database level via triggers and constraints, not just in application code.
- **Layered validation** — Input is validated at the application layer before being passed to the database, which applies its own constraints.

---

## Database Design

### Core Tables

| Table              | Purpose                                           |
|--------------------|---------------------------------------------------|
| `USERS`            | User accounts, hashed passwords, roles            |
| `PRODUCTS`         | Shoe catalog (name, brand, price, stock, images)  |
| `CATEGORIES`       | Product categories for filtering                  |
| `CART`             | Active shopping cart items per user                |
| `ORDERS`           | Order header (user, date, total, status)          |
| `ORDER_DETAILS`    | Line items for each order                         |
| `FACE_AUTH`        | Stored facial encoding data for biometric login   |
| `QR_SESSIONS`      | QR login tokens with expiration timestamps        |
| `AUDIT_LOG`        | Tracks sensitive operations for security auditing |

### Key Relationships

```
USERS (1) -----> (N) ORDERS -----> (N) ORDER_DETAILS -----> (1) PRODUCTS
  |                                                              |
  +--> (N) CART ------------------------------------------------+
  |
  +--> (1) FACE_AUTH
  |
  +--> (N) QR_SESSIONS
```

### Constraints and Integrity Rules

- **Primary keys** on all tables with auto-incrementing sequences
- **Foreign keys** enforcing referential integrity between orders, users, and products
- **CHECK constraints** on price (> 0), stock quantity (>= 0), and order status values
- **UNIQUE constraints** on email and username fields
- **NOT NULL constraints** on critical fields (password hash, product name, order total)

---

## Security Design

This section details how each security mechanism is implemented and why it matters.

### 1. Password Hashing

**Implementation:** User passwords are hashed using a one-way cryptographic hash function (such as SHA-256 with salt or bcrypt) before storage. During login, the submitted password is hashed and compared against the stored hash.

**Why it matters:** If the database is compromised, attackers cannot recover plaintext passwords. Salting prevents rainbow table attacks.

```sql
-- Stored procedure for user registration
CREATE OR REPLACE PROCEDURE SP_REGISTER_USER(
    p_username  IN VARCHAR2,
    p_email     IN VARCHAR2,
    p_password  IN VARCHAR2,
    p_role      IN VARCHAR2 DEFAULT 'customer'
) AS
    v_hashed_pw VARCHAR2(256);
BEGIN
    v_hashed_pw := DBMS_CRYPTO.HASH(
        UTL_I18N.STRING_TO_RAW(p_password, 'AL32UTF8'),
        DBMS_CRYPTO.HASH_SH256
    );
    INSERT INTO USERS (username, email, password_hash, role, created_at)
    VALUES (p_username, p_email, v_hashed_pw, p_role, SYSDATE);
    COMMIT;
END;
```

### 2. SQL Injection Prevention

**Implementation:** The application never constructs SQL queries by concatenating user input. All database operations are performed through:
- **Stored procedures** called with bound parameters
- **ADO.NET parameterized commands** in the application layer

**Why it matters:** SQL injection is consistently ranked among the top web application vulnerabilities (OWASP Top 10). By using parameterized queries exclusively, user input is always treated as data, never as executable code.

```csharp
// C# — parameterized call to stored procedure
using (var cmd = new OracleCommand("SP_AUTHENTICATE_USER", connection))
{
    cmd.CommandType = CommandType.StoredProcedure;
    cmd.Parameters.Add("p_username", OracleDbType.Varchar2).Value = username;
    cmd.Parameters.Add("p_password", OracleDbType.Varchar2).Value = hashedPassword;
    cmd.Parameters.Add("p_result", OracleDbType.Int32).Direction = ParameterDirection.Output;
    cmd.ExecuteNonQuery();
}
```

### 3. Role-Based Access Control (RBAC)

**Implementation:** Each user is assigned a role (`admin` or `customer`) stored in the `USERS` table. The application checks the user's role before granting access to protected resources. At the database level, stored procedures verify the caller's role before executing sensitive operations.

**Why it matters:** RBAC ensures that users can only perform actions appropriate to their role. A customer cannot access admin functions like deleting products or viewing all user accounts, even if they attempt to call the endpoint directly.

```sql
-- Stored procedure that enforces admin-only access
CREATE OR REPLACE PROCEDURE SP_DELETE_PRODUCT(
    p_user_id    IN NUMBER,
    p_product_id IN NUMBER
) AS
    v_role VARCHAR2(20);
BEGIN
    SELECT role INTO v_role FROM USERS WHERE user_id = p_user_id;
    IF v_role != 'admin' THEN
        RAISE_APPLICATION_ERROR(-20001, 'Access denied: admin privileges required');
    END IF;
    DELETE FROM PRODUCTS WHERE product_id = p_product_id;
    COMMIT;
END;
```

### 4. Stored Procedures

**Implementation:** All CRUD operations and business logic are encapsulated in PL/SQL stored procedures. The application layer calls these procedures rather than executing ad-hoc SQL statements.

**Why it matters:**
- Reduces the attack surface by preventing direct table access
- Centralizes business logic in the database, making it easier to audit and maintain
- Enables fine-grained access control at the procedure level

### 5. Database Triggers

**Implementation:** Triggers are used to automatically enforce business rules that must always hold, regardless of how data is modified.

**Why it matters:** Triggers provide a safety net that operates independently of the application code. Even if a bug in the application attempts an invalid operation, the trigger will prevent it.

```sql
-- Prevent orders with invalid quantities
CREATE OR REPLACE TRIGGER TRG_VALIDATE_ORDER_DETAIL
BEFORE INSERT ON ORDER_DETAILS
FOR EACH ROW
BEGIN
    IF :NEW.quantity <= 0 THEN
        RAISE_APPLICATION_ERROR(-20002, 'Order quantity must be greater than zero');
    END IF;
    -- Verify sufficient stock
    DECLARE
        v_stock NUMBER;
    BEGIN
        SELECT stock INTO v_stock FROM PRODUCTS WHERE product_id = :NEW.product_id;
        IF v_stock < :NEW.quantity THEN
            RAISE_APPLICATION_ERROR(-20003, 'Insufficient stock for this product');
        END IF;
    END;
END;

-- Log sensitive operations for audit
CREATE OR REPLACE TRIGGER TRG_AUDIT_USER_CHANGES
AFTER INSERT OR UPDATE OR DELETE ON USERS
FOR EACH ROW
BEGIN
    INSERT INTO AUDIT_LOG (table_name, operation, performed_by, performed_at, details)
    VALUES ('USERS', 
            CASE WHEN INSERTING THEN 'INSERT' WHEN UPDATING THEN 'UPDATE' ELSE 'DELETE' END,
            USER, SYSDATE,
            'user_id: ' || NVL(TO_CHAR(:NEW.user_id), TO_CHAR(:OLD.user_id)));
END;
```

### 6. Transaction Management

**Implementation:** Order processing is wrapped in a database transaction. The system inserts the order header, creates order detail records, updates product stock, and clears the user's cart — all within a single atomic transaction. If any step fails, the entire operation is rolled back.

**Why it matters:** Without transactions, a failure mid-process could result in an order being created without updating stock, or stock being decremented without a corresponding order. Transactions guarantee data consistency.

```sql
CREATE OR REPLACE PROCEDURE SP_PLACE_ORDER(
    p_user_id IN NUMBER,
    p_order_id OUT NUMBER
) AS
    v_total NUMBER := 0;
BEGIN
    -- Create order header
    INSERT INTO ORDERS (user_id, order_date, status, total_amount)
    VALUES (p_user_id, SYSDATE, 'pending', 0)
    RETURNING order_id INTO p_order_id;

    -- Move cart items to order details and calculate total
    FOR item IN (SELECT * FROM CART WHERE user_id = p_user_id) LOOP
        INSERT INTO ORDER_DETAILS (order_id, product_id, quantity, unit_price)
        VALUES (p_order_id, item.product_id, item.quantity, item.unit_price);
        
        UPDATE PRODUCTS SET stock = stock - item.quantity 
        WHERE product_id = item.product_id;
        
        v_total := v_total + (item.quantity * item.unit_price);
    END LOOP;

    -- Update order total
    UPDATE ORDERS SET total_amount = v_total WHERE order_id = p_order_id;

    -- Clear cart
    DELETE FROM CART WHERE user_id = p_user_id;

    COMMIT;
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE;
END;
```

### 7. Face Recognition Login

**Implementation:** Users can register their facial data as an alternative authentication method. During login, the system captures a facial image, extracts feature encodings, and compares them against the stored encoding using a similarity threshold.

**How it works:**
1. During registration, the user's facial encoding is computed and stored in the `FACE_AUTH` table
2. At login, the captured image is processed to extract encodings
3. The system compares the encodings against stored data using a distance metric
4. If the similarity score exceeds the threshold, the user is authenticated

**Why it matters:** Biometric authentication adds a layer of security that cannot be shared, guessed, or easily stolen — unlike passwords. It also demonstrates integration of modern authentication techniques with a traditional database backend.

### 8. QR Code Login

**Implementation:** The system generates a unique, time-limited QR code containing a session token. The user scans the QR code with an authenticated device, which sends the token back to the server to establish the session.

**How it works:**
1. Server generates a unique token and stores it in `QR_SESSIONS` with an expiration timestamp
2. The token is encoded into a QR code and displayed on the login page
3. The user scans the QR code using a mobile device that is already authenticated
4. The server validates the token, checks expiration, and establishes the session
5. The token is invalidated after use (single-use)

```sql
-- Generate QR session token
CREATE OR REPLACE PROCEDURE SP_CREATE_QR_SESSION(
    p_token OUT VARCHAR2,
    p_expires_at OUT TIMESTAMP
) AS
BEGIN
    p_token := DBMS_RANDOM.STRING('X', 64);
    p_expires_at := SYSTIMESTAMP + INTERVAL '5' MINUTE;
    INSERT INTO QR_SESSIONS (token, status, created_at, expires_at)
    VALUES (p_token, 'pending', SYSTIMESTAMP, p_expires_at);
    COMMIT;
END;
```

**Why it matters:** QR login is a passwordless authentication method that reduces exposure to credential-based attacks. It demonstrates understanding of token-based session management and time-bound security mechanisms.

### 9. Server-Side Input Validation

**Implementation:** All user inputs are validated on the server before being processed or passed to the database. Validation includes type checking, length limits, format validation (e.g., email regex), and sanitization of special characters.

**Why it matters:** Client-side validation can be bypassed trivially. Server-side validation is the real defense against malformed or malicious input.

### 10. Secure Session Handling

**Implementation:** Sessions are managed server-side with the following practices:
- Session IDs are generated using cryptographically secure random values
- Sessions expire after a configurable period of inactivity
- Session data is stored server-side (not in cookies)
- Sessions are invalidated on logout

**Why it matters:** Poor session management can lead to session hijacking or fixation attacks. Server-side session storage ensures that sensitive data is never exposed to the client.

---

## Key Workflows

### Login Flow (Standard)

```
User submits credentials
    --> Server validates input format
    --> Hash submitted password
    --> Call SP_AUTHENTICATE_USER with hashed password
    --> Compare against stored hash in USERS table
    --> If match: create server-side session, redirect to dashboard
    --> If no match: return error message (generic, no hints)
```

### Login Flow (Face Recognition)

```
User selects face login
    --> Camera captures facial image
    --> Extract facial encoding via recognition API
    --> Query FACE_AUTH table for matching encoding
    --> If similarity > threshold: create session, authenticate
    --> If no match: prompt for alternative login method
```

### Login Flow (QR Code)

```
User selects QR login
    --> Server calls SP_CREATE_QR_SESSION, generates token
    --> QR code displayed on screen with embedded token
    --> User scans QR with authenticated mobile device
    --> Mobile sends token to server
    --> Server validates token + checks expiration
    --> If valid: bind session to user, authenticate on browser
    --> Token marked as 'used' (single-use)
```

### Order Processing Flow

```
Customer clicks "Place Order"
    --> Server validates cart is not empty
    --> Call SP_PLACE_ORDER (runs as transaction):
        1. Create order record in ORDERS
        2. Copy cart items to ORDER_DETAILS
        3. Trigger TRG_VALIDATE_ORDER_DETAIL fires:
           - Validates quantity > 0
           - Checks stock availability
        4. Deduct stock from PRODUCTS
        5. Calculate and update order total
        6. Clear user's CART
        7. COMMIT
    --> If any step fails: ROLLBACK entire transaction
    --> Return order confirmation to user
```

---

## Challenges and Solutions

### 1. Preventing SQL Injection Without an ORM

**Challenge:** The project uses ADO.NET directly rather than an ORM like Entity Framework, which means SQL injection prevention had to be handled manually.

**Solution:** Adopted a strict architecture rule: all database interactions must go through stored procedures with parameterized binding. No raw SQL string concatenation exists anywhere in the codebase. Code reviews specifically checked for this.

### 2. Implementing Face Recognition with Oracle Database

**Challenge:** Oracle Database does not natively support facial encoding comparison. Integrating a biometric system with a relational database required bridging two very different technologies.

**Solution:** Facial encoding is handled at the application layer using a recognition library. The resulting encoding vector is serialized and stored in the `FACE_AUTH` table. Comparison is performed at the application layer, and only the authentication result is passed back to the database to create a session.

### 3. Ensuring Atomic Order Processing

**Challenge:** Order placement involves multiple tables (ORDERS, ORDER_DETAILS, PRODUCTS, CART). A failure at any point could leave the database in an inconsistent state.

**Solution:** Wrapped the entire order flow in a single PL/SQL procedure with explicit COMMIT and ROLLBACK. Added triggers as an additional safety layer to reject invalid data before it enters the tables.

### 4. Balancing Security with Usability

**Challenge:** Strict security measures (complex passwords, multi-step authentication) can frustrate users.

**Solution:** Offered multiple login methods (password, face recognition, QR code) so users can choose their preferred balance of convenience and security. Password requirements are enforced but reasonable.

---

## Testing

### Security Testing

| Test Case                        | Method                                              | Expected Result                          |
|----------------------------------|------------------------------------------------------|------------------------------------------|
| SQL injection via login form     | Submit `' OR '1'='1` as username                    | Login fails; input treated as literal    |
| SQL injection via search         | Submit `'; DROP TABLE PRODUCTS; --` in search field | Query fails safely; no table affected    |
| Unauthorized admin access        | Customer attempts to call admin stored procedure    | `RAISE_APPLICATION_ERROR` blocks access  |
| Direct URL access to admin pages | Navigate to `/admin/dashboard` without admin role   | Redirect to login or 403 error           |
| Order with zero quantity         | Attempt to insert order detail with quantity = 0    | Trigger rejects the insert               |
| Order exceeding stock            | Order 100 units of item with 5 in stock             | Trigger rejects with insufficient stock  |
| Expired QR token                 | Scan QR code after 5-minute window                  | Authentication fails; token expired      |
| Reuse of QR token                | Scan same QR code twice                             | Second attempt rejected; token consumed  |
| Session timeout                  | Leave session idle beyond timeout period            | Session invalidated; redirect to login   |

### Functional Testing

- Verified all CRUD operations for products, users, and orders
- Tested shopping cart add/update/remove with concurrent sessions
- Confirmed order total calculation accuracy across various cart contents
- Validated search and filter functionality with edge-case inputs

---

## Future Improvements

- **HTTPS enforcement** — Encrypt all client-server communication using TLS
- **Two-Factor Authentication (2FA)** — Add OTP verification via email or SMS for sensitive operations
- **Rate limiting** — Prevent brute-force login attempts by throttling failed authentication requests
- **Online payment integration** — Support credit card and digital wallet payments with PCI DSS compliance considerations
- **Database encryption** — Implement Transparent Data Encryption (TDE) for data-at-rest protection
- **Comprehensive audit logging** — Expand the audit trail to cover all data modifications with before/after snapshots
- **API layer** — Expose a RESTful API with token-based authentication (JWT) for mobile client support

---

## Conclusion

This project was built to go beyond a typical e-commerce exercise. The primary objective was to apply **database security concepts in a working system** — not just in isolated lab exercises, but integrated into real features that users interact with.

**Key takeaways from building this system:**

- **Security must be designed in, not bolted on.** Deciding to use stored procedures and parameterized queries from the start shaped the entire architecture and made the system fundamentally more resistant to injection attacks.
- **The database is a security boundary.** By enforcing access control and business rules at the database level (via RBAC checks in procedures, triggers, and constraints), the system remains secure even if the application layer has bugs.
- **Authentication can be multi-modal.** Implementing face recognition and QR code login alongside traditional password authentication provided practical experience with different security paradigms and their trade-offs.
- **Transactions are not optional.** The order processing workflow demonstrated why atomicity matters — a partially completed order is worse than a failed one.

This project reflects hands-on experience with Oracle Database administration, PL/SQL development, secure application architecture, and the practical challenges of building a system where security is a first-class requirement.
