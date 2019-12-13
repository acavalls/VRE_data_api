<?php
/**
 * @SWG\SecurityScheme(
 *   securityDefinition="bearer",
 *   description="This API expects users to authentificate via bearer tokens. Obtain them at https://vre.eucanshare.bsc.es,</p><p>1 - login using any of the configured OAuth2 providers and your token will be displayed at 'My Profile' &rarr; 'API Keys' section.</p><p>2 - Copy&paste it here in the following format: 'bearer access_token_string'",
 *   type="apiKey",
 *   name="Authorization",
 *   in="header"
 * )
 * @SWG\SecurityScheme(
 *   securityDefinition="vre_auth",
 *   description="This API expects users to authentificate via bearer tokens obtained via OAuth2. If you have an authorized OAuth2 client, you can generate them here. If not, open the OAuth2 dialog at http://vre.eucanshare.bsc.es to obtain them, as detailed in the above section<br/>",
 *   type="oauth2",
 *   authorizationUrl="https://inb.bsc.es/auth/realms/eucanshare/protocol/openid-connect/userinfo",
 *   tokenUrl="https://inb.bsc.es/auth/realms/eucanshare/protocol/openid-connect/token",
 *   flow="accessCode",
 *   scopes={
 *        "read" : "read your files",
 *   }
 * )
 */
