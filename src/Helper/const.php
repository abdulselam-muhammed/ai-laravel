<?php

const ACTIVE = 1;
const INITIATE = 2;
const DEACTIVATE = 0;

// User role
const USER_ROLE_ADMIN = 1;
const USER_ROLE_USER = 2;

// Gateway mode
const GATEWAY_MODE_LIVE = 1;
const GATEWAY_MODE_SANDBOX = 2;

// User status
const USER_STATUS_ACTIVE = 1;
const USER_STATUS_INACTIVE = 0;

// Ticket status
const TICKET_STATUS_OPEN = 1;
const TICKET_STATUS_INPROGRESS = 2;
const TICKET_STATUS_CLOSE = 3;
const TICKET_STATUS_REOPEN = 4;
const TICKET_STATUS_RESOLVED = 5;

// Invoice status
const INVOICE_STATUS_PENDING = 0;
const INVOICE_STATUS_PAID = 1;
const INVOICE_STATUS_OVER_DUE = 2;

const INVOICE_RECURRING_TYPE_MONTHLY = 1;
const INVOICE_RECURRING_TYPE_YEARLY = 2;
const INVOICE_RECURRING_TYPE_CUSTOM = 3;

// Order payment status
const ORDER_PAYMENT_STATUS_PENDING = 0;
const ORDER_PAYMENT_STATUS_PAID = 1;
const ORDER_PAYMENT_STATUS_CANCELLED = 2;

const PACKAGE_DURATION_TYPE_MONTHLY = 1;
const PACKAGE_DURATION_TYPE_YEARLY = 2;

const REACT_LIKE = 1;
const REACT_DISLIKE = 2;

// Message
const SOMETHING_WENT_WRONG = "Something went wrong! Please try again";
const CREATED_SUCCESSFULLY = "Created Successfully";
const UPDATED_SUCCESSFULLY = "Updated Successfully";
const STATUS_UPDATED_SUCCESSFULLY = "Status Updated Successfully";
const DELETED_SUCCESSFULLY = "Deleted Successfully";
const UPLOADED_SUCCESSFULLY = "Uploaded Successfully";
const DATA_FETCH_SUCCESSFULLY = "Data Fetch Successfully";
const SENT_SUCCESSFULLY = "Sent Successfully";
const PAY_SUCCESSFULLY = "Pay Successfully";

//Gateway name
const PAYPAL = 'paypal';
const STRIPE = 'stripe';
const RAZORPAY = 'razorpay';
const INSTAMOJO = 'instamojo';
const MOLLIE = 'mollie';
const PAYSTACK = 'paystack';
const SSLCOMMERZ = 'sslcommerz';
const MERCADOPAGO = 'mercadopago';
const FLUTTERWAVE = 'flutterwave';
const BANK = 'bank';
const WALLET = 'wallet';
const IYZICO = 'iyzico';

const RULES_CHARACTER = 1;
const RULES_LANGUAGE = 2;
const RULES_USE_CASE = 3;
const RULES_TONE = 4;
const RULES_PLAN_REMAINING_DAYS = 5;

// contact message status
const CONTACT_MESSAGE_STATUS_PENDING = 0;
const CONTACT_MESSAGE_STATUS_REPLIED = 1;
const CONTACT_MESSAGE_STATUS_CANCEL = 2;
