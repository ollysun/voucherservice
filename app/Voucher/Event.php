<?php namespace Voucher\Voucher;

//@TODO THIS CLASS CAN MOVE TO COMMON-LIB, IF ANALYTICS NEEDS IT
class Event
{

    // GENERIC EVENTS
    const SUBSCRIPTION_INITIATED = 'Subscription Initiated';
    const SUBSCRIPTION_STARTED = 'Subscription Started';

    const SUBSCRIPTION_RESTART_INITIATED = 'Subscription Restart Initiated';
    const SUBSCRIPTION_RESTARTED = 'Subscription Restarted';

    const SUBSCRIPTION_RENEWED = 'Subscription Renewed';

    const ONE_TIME_PAYMENT_INITIATED = 'One Time Payment Initiated';
    const ONE_TIME_PAYMENT_REINITIATED = 'One Time Payment ReInitiated';
    const ONE_TIME_PAYMENT_STARTED = 'One Time Payment Started';
    const ONE_TIME_PAYMENT_RESTARTED = 'One Time Payment ReStarted';

    const SUBSCRIPTION_CANCEL_INITIATED = 'Subscription cancel Initiated';
    const SUBSCRIPTION_CANCELLED = 'Subscription Cancelled';

    const SUBSCRIPTION_RECANCEL_INITIATED = 'Subscription Recancell Initiated';
    const SUBSCRIPTION_RECANCELLED = 'Subscription ReCancelled';

    const PAYMENT_FAILED = 'Payment Failed';
    const PAYMENT_REFUNDED = 'Payment Refunded';

    const CUSTOMER_CREATED = 'Customer Created';

    const DISPUTE_CREATED = 'Dispute Created';
    const DISPUTE_CLOSED = 'Dispute Closed';
    const DISPUTE_CLOSED_WON = 'Payment Refunded';

    const INVOICE_PAYMENT_SUCCEEDED = 'Invoice Payment Succeeded';

    const EVENT_DISCREPANCY = 'Event Discrepancy';

    // GENERIC STATUS
    const STATUS_PENDING = 'pending';
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_RENEWED = 'renewed';
    const STATUS_ONETIME_PAYMENT = 'one off charged';
    const STATUS_FREE = 'free';
    const STATUS_GRACE = 'grace';
    const STATUS_UPDATED = 'updated';
    const STATUS_RESTARTED = 'restarted';

    const STATUS_CANCELLING = 'cancelling';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_DISPUTE_CREATED = 'dispute_created';
    const STATUS_DISPUTE_CLOSED = 'dispute_closed';
    const STATUS_ERROR = 'error';
}
