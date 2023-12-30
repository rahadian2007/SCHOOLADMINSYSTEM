<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'partnerServiceId', 'customerNo', 'virtualAccountNumber', 'virtualAccountName', 'trxId', 'paymentRequestId', 'channelCode', 'paidAmount', 'externalId', 'paymentFlagStatus'
    ];

    public function va() {
        return $this->hasOne('\App\Models\VirtualAccount', 'number', 'virtualAccountNumber');
    }
}
