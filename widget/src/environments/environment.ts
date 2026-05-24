/**
 * Widget-Konfiguration.
 *
 *  • apiUrl — Basis-URL der Backend-API. Im Dev über den Angular-Proxy.
 *
 *  Der Public Key wird IMMER per URL-Parameter übergeben:
 *    http://localhost:4201/?key=<publicKey>
 *
 *  Den korrekten Key findest du im Admin unter Einstellungen → Widget Designer
 *  (unten im "Widget einbinden"-Block).
 */
export const environment = {
  production: false,
  apiUrl: '/api/v1',
  publicKey: '',   // Kein Fallback — Key muss immer per ?key= übergeben werden
};
