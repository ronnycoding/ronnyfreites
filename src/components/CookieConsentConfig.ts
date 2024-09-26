import type { CookieConsentConfig } from 'vanilla-cookieconsent';

declare global {
    interface Window {
        dataLayer: Record<string, any>[];
        gtag: (...args: any[]) => void;
    }
}

export const config: CookieConsentConfig = {
    // Indicate the consent to live in the #cc-container element
    root: '#cc-container',
    guiOptions: {
        consentModal: {
            layout: 'box inline',
            position: 'bottom center',
            equalWeightButtons: true,
            flipButtons: false
        },
        preferencesModal: {
            layout: 'box',
            position: 'right',
            equalWeightButtons: true,
            flipButtons: false
        }
    },
    categories: {
        necessary: {
            readOnly: true
        },
        analytics: {
            services: {
                ga4: {
                    label: '<a href="https://marketingplatform.google.com/about/analytics/terms/us/" target="_blank">Google Analytics 4</a>',
                    onAccept: () => {
                        // Grant consent to the Google Analytics service
                        console.log('ga4 granted');

                        window.gtag('consent', 'update', {
                            ad_storage: 'granted',
                            ad_user_data: 'granted',
                            ad_personalization: 'granted',
                            analytics_storage: 'granted'
                        });
                    },
                    onReject: () => {
                        // Don't enable Google Analytics
                        console.log('ga4 rejected');
                    },
                    cookies: [
                        {
                            name: /^_ga/
                        }
                    ]
                }
            }
        }
    },
    language: {
        default: 'es',
        autoDetect: 'browser',
        translations: {
            en: {
                consentModal: {
                    title: 'We use cookies to improve your experience',
                    description: 'Do you accept the use of cookies?',
                    acceptAllBtn: 'Accept',
                    acceptNecessaryBtn: 'Reject',
                    showPreferencesBtn: 'Manage preferences'
                },
                preferencesModal: {
                    title: 'Cookie Preferences',
                    acceptAllBtn: 'Accept all',
                    acceptNecessaryBtn: 'Reject all',
                    savePreferencesBtn: 'Save preferences',
                    closeIconLabel: 'Close',
                    sections: [
                        {
                            title: "Necessary Cookies <span class='pm__badge'>Always Enabled</span>",
                            description: 'These cookies are essential for the website to function properly.',
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Analytics Cookies',
                            description: 'These cookies help us understand how visitors interact with the website.',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'More information',
                            description: "For any queries about our cookie policy, please <a class='cc__link' href='#contact'>contact us</a>."
                        }
                    ]
                }
            },
            es: {
                consentModal: {
                    title: 'Utilizamos cookies para mejorar tu experiencia',
                    description: '¿Aceptas el uso de cookies?',
                    acceptAllBtn: 'Aceptar',
                    acceptNecessaryBtn: 'Rechazar',
                    showPreferencesBtn: 'Gestionar preferencias'
                },
                preferencesModal: {
                    title: 'Preferencias de cookies',
                    acceptAllBtn: 'Aceptar todas',
                    acceptNecessaryBtn: 'Rechazar todas',
                    savePreferencesBtn: 'Guardar preferencias',
                    closeIconLabel: 'Cerrar',
                    sections: [
                        {
                            title: "Cookies necesarias <span class='pm__badge'>Siempre activadas</span>",
                            description: 'Estas cookies son esenciales para el funcionamiento del sitio web.',
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Cookies analíticas',
                            description: 'Estas cookies nos ayudan a entender cómo los visitantes interactúan con el sitio web.',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'Más información',
                            description:
                                "Para cualquier consulta sobre nuestra política de cookies, por favor <a class='cc__link' href='#contacto'>contáctenos</a>."
                        }
                    ]
                }
            }
        }
    }
};
