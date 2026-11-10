<?php

return [
    'add' => 'Request a vessel addition or update',
    'instructions' => 'Use the IMO number of the vessel. HAG reviews the request before it can be used in sailing schedules.',
    'type' => 'Vessel type',
    'submit' => 'Submit for review',
    'history' => 'Vessel requests',
    'empty' => 'No vessel requests yet.',
    'duplicate' => 'A request for this IMO is already awaiting review.',
    'submitted' => 'The vessel request was submitted.',
    'conflict' => 'The carrier is inactive or the IMO belongs to a different record. Resolve the assignment before approving.',
    'reviewed' => 'The vessel request was reviewed.',
    'review_queue' => 'Carrier vessel requests',
    'note' => 'Review note (required when rejecting)',
    'approve' => 'Approve',
    'reject' => 'Reject',
    'existing' => 'Existing vessel',
    'unassigned' => 'Unassigned',
    'statuses' => [
        'pending' => 'Pending review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],
];
