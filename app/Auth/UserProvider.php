<?php
namespace Sponsor\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Sponsor\Models\Sponsor;

class UserProvider extends EloquentUserProvider
{
    /**
     * (non-PHPdoc)
     * @see \Illuminate\Auth\EloquentUserProvider::validateCredentials()
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if ($user instanceof Sponsor) {
            $plain = $credentials['password'];
            
            return $this->hasher->check($plain, $user->getAuthPassword(), [
                'salt' => $user->salt 
            ]);
        }
        
        return parent::validateCredentials($user, $credentials);
    }
}