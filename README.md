#SmartMed Pharmacy Chain Management System

This repository contains the database design and related components for the SmartMed Pharmacy Chain Management System, a centralized and advanced database system designed for a rapidly expanding retail pharmacy chain in Sri Lanka.

##Project Overview

The healthcare and pharmaceutical industry is a critical, data-intensive sector where accuracy, availability, and speed of information are vital. This project addresses the real-world challenges of managing a pharmacy chain by designing and implementing a comprehensive database system that not only supports operational efficiency but also includes advanced features like triggers, stored procedures, views, and business intelligence components.


SmartMed Pvt Ltd operates a network of outlets across Sri Lanka with headquarters in Colombo. They offer a wide range of prescription drugs, over-the-counter (OTC) medications, healthcare products, and wellness solutions. The system also integrates with an e-commerce platform for online purchases, digital prescription uploads, and home delivery services.



Key Features
The database system is designed to efficiently manage the following core business components:

Customer and patient data 
Inventory and stock levels across outlets 
Orders and prescriptions (both physical and digital) 
Employees, pharmacists, and outlet performance 
Vendor and supplier transactions 
Billing and payment records 
Business Intelligence for data-driven decision-making, including customer behavior analysis and product sales forecasting 
Database Design
The database design follows a rigorous normalization process up to 3NF to ensure data integrity and efficiency. Below are the key entities and their normalized structures:

Relational Mapping (ER Diagram to Tables) 
Customer: Customer(CustomerID PK, Name, DOB, Gender, Phone, Email, Address) 
Employee: Employee(EmployeeID PK, Name, Position, ContactNumber, Email, HireDate, Salary, OutletID FK) 
Pharmacist: Pharmacist(PharmacistID PK, LicenseNumber, Qualification) (Specialized Employee) 
Outlet: Outlet(OutletID PK, Location, ContactNumber, ManagerID FK) 
Product: Product(ProductID PK, ProductName, Description, Category, Requires Prescription, UnitPrice, Expiry Date) 
Vendor: Vendor(VendorID PK, CompanyName, ContactPerson, Phone, Email, Address) 
Order: Order(OrderID PK, CustomerID FK, OrderDate, TotalAmount, PaymentStatus, Delivery Method) 
OrderDetails: OrderDetails(OrderID PK, ProductID PK, Quantity, UnitPrice, Discount) (Weak Entity) 
Prescription: Prescription(PrescriptionID PK, CustomerID FK, PharmacistID FK, IssuedDate, Expiry Date, Notes) 

PrescriptionItem: PrescriptionItem(PrescriptionID PK, ProductID PK, Dosage, Frequency, Duration) 

Delivery: Delivery(DeliveryID PK, OrderID FK, DeliveryDate, Delivery Status, DeliveryAddress) 

Supply: Supply(SupplyID, VendorID, ProductID, SupplyDate, Quantity Supplied, CostPerUnit) 
Data Normalization Steps (Up to 3NF) 
The document details the normalization process for each major entity, demonstrating the transformation from Unnormalized Form (UNF) to 1NF, 2NF, and 3NF, addressing multivalued attributes, partial dependencies, and transitive dependencies.

Assumptions
The project is built upon the following assumptions:

Each prescription is linked to one order only.
Each outlet maintains its own inventory.
Some products may require a prescription.
Each employee is assigned to a single outlet.
