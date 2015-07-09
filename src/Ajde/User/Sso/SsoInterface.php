<?php


namespace Ajde\User\Sso;




interface Ajde_User_Sso_Interface
{
    public static function getIconName();
    public static function getColor();

    public function destroySession();

    public function getAuthenticationURL();
    public function isAuthenticated();

    /**
     * @return Ajde_User
     */
    public function getUser();

    public function getUsernameSuggestion();
    public function getEmailSuggestion();
    public function getNameSuggestion();
    public function getAvatarSuggestion();
    public function getProfileSuggestion();

    public function getUidHash();
    public function getData();
}