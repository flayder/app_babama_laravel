<?php

declare(strict_types=1);

// StripeJS singleton
require __DIR__.'/lib/StripeJS.php';

// Utilities
require __DIR__.'/lib/Util/AutoPagingIterator.php';
require __DIR__.'/lib/Util/LoggerInterface.php';
require __DIR__.'/lib/Util/DefaultLogger.php';
require __DIR__.'/lib/Util/RequestOptions.php';
require __DIR__.'/lib/Util/Set.php';
require __DIR__.'/lib/Util/Util.php';

// HttpClient
require __DIR__.'/lib/HttpClient/ClientInterface.php';
require __DIR__.'/lib/HttpClient/CurlClient.php';

// Errors
require __DIR__.'/lib/Error/Base.php';
require __DIR__.'/lib/Error/Api.php';
require __DIR__.'/lib/Error/ApiConnection.php';
require __DIR__.'/lib/Error/Authentication.php';
require __DIR__.'/lib/Error/Card.php';
require __DIR__.'/lib/Error/InvalidRequest.php';
require __DIR__.'/lib/Error/Permission.php';
require __DIR__.'/lib/Error/RateLimit.php';
require __DIR__.'/lib/Error/SignatureVerification.php';

// Plumbing
require __DIR__.'/lib/ApiResponse.php';
require __DIR__.'/lib/JsonSerializable.php';
require __DIR__.'/lib/StripeJSObject.php';
require __DIR__.'/lib/ApiRequestor.php';
require __DIR__.'/lib/ApiResource.php';
require __DIR__.'/lib/SingletonApiResource.php';
require __DIR__.'/lib/AttachedObject.php';
require __DIR__.'/lib/ExternalAccount.php';

// StripeJS API Resources
require __DIR__.'/lib/Account.php';
require __DIR__.'/lib/AlipayAccount.php';
require __DIR__.'/lib/ApplePayDomain.php';
require __DIR__.'/lib/ApplicationFee.php';
require __DIR__.'/lib/ApplicationFeeRefund.php';
require __DIR__.'/lib/Balance.php';
require __DIR__.'/lib/BalanceTransaction.php';
require __DIR__.'/lib/BankAccount.php';
require __DIR__.'/lib/BitcoinReceiver.php';
require __DIR__.'/lib/BitcoinTransaction.php';
require __DIR__.'/lib/Card.php';
require __DIR__.'/lib/Charge.php';
require __DIR__.'/lib/Collection.php';
require __DIR__.'/lib/CountrySpec.php';
require __DIR__.'/lib/Coupon.php';
require __DIR__.'/lib/Customer.php';
require __DIR__.'/lib/Dispute.php';
require __DIR__.'/lib/Event.php';
require __DIR__.'/lib/FileUpload.php';
require __DIR__.'/lib/Invoice.php';
require __DIR__.'/lib/InvoiceItem.php';
require __DIR__.'/lib/LoginLink.php';
require __DIR__.'/lib/Order.php';
require __DIR__.'/lib/OrderReturn.php';
require __DIR__.'/lib/Payout.php';
require __DIR__.'/lib/Plan.php';
require __DIR__.'/lib/Product.php';
require __DIR__.'/lib/Recipient.php';
require __DIR__.'/lib/RecipientTransfer.php';
require __DIR__.'/lib/Refund.php';
require __DIR__.'/lib/SKU.php';
require __DIR__.'/lib/Source.php';
require __DIR__.'/lib/Subscription.php';
require __DIR__.'/lib/SubscriptionItem.php';
require __DIR__.'/lib/ThreeDSecure.php';
require __DIR__.'/lib/Token.php';
require __DIR__.'/lib/Transfer.php';
require __DIR__.'/lib/TransferReversal.php';
require __DIR__.'/lib/Webhook.php';
require __DIR__.'/lib/WebhookSignature.php';
