<?php

namespace App\Services;

use App\Services\Agora\RtcTokenBuilder;

class AgoraService
{
    protected $appId;
    protected $appCertificate;

    public function __construct()
    {
        $this->appId = config('agora.app_id');
        $this->appCertificate = config('agora.app_certificate');
    }

    public function generateToken($channelName, $uid, $expireTime = 3600)
    {
        $role = RtcTokenBuilder::RolePublisher;
        $privilegeExpiredTs = now()->timestamp + $expireTime;

        return RtcTokenBuilder::buildTokenWithUid(
            $this->appId,
            $this->appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );
    }
}
