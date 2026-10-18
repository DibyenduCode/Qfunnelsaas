<?php

use App\Core\Router;
use App\Core\Response;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\FunnelController;
use App\Controllers\BuilderController;
use App\Controllers\SubmissionController;
use App\Controllers\AnalyticsController;
use App\Controllers\SettingsController;
use App\Controllers\TeamController;
use App\Controllers\WebhookController;
use App\Controllers\PlatformAdminController;
use App\Controllers\QuizController;
use App\Controllers\ApiController;
use App\Controllers\LegalController;
use App\Middleware\AuthMiddleware;
use App\Middleware\TenantMiddleware;
use App\Middleware\PlatformAdminMiddleware;
use App\Middleware\CsrfMiddleware;

/** @var Router $router */

// Home Landing Page
$router->get('/', [HomeController::class, 'index']);

// Authentication Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// Public Quiz Routes
$router->get('/quiz/{slug}', [QuizController::class, 'show']);
$router->get('/quiz/{slug}/result/{token}', [QuizController::class, 'result']);

// Public Quiz API Endpoints
$router->post('/api/quiz/{slug}/init', [QuizController::class, 'apiInit']);
$router->get('/api/quiz/step', [QuizController::class, 'apiStep']);
$router->post('/api/quiz/form', [QuizController::class, 'apiSubmitForm']);
$router->post('/api/quiz/answer', [QuizController::class, 'apiSubmitAnswer']);
$router->post('/api/quiz/back', [QuizController::class, 'apiBack']);

// Public Dynamic Legal & Compliance Pages
$router->get('/terms', [LegalController::class, 'terms']);
$router->get('/privacy', [LegalController::class, 'privacy']);
$router->get('/refund-policy', [LegalController::class, 'refundPolicy']);
$router->get('/cookie-policy', [LegalController::class, 'cookiePolicy']);
$router->get('/legal/{slug}', [LegalController::class, 'show']);

// Public Documentation Page (API, Webhooks & Embedding)
$router->get('/docs', [HomeController::class, 'docs']);
$router->get('/documentation', [HomeController::class, 'docs']);

// External Workspace REST API (v1) - Bearer / API-Key Token Authenticated
$router->get('/api/v1/me', [ApiController::class, 'me']);
$router->get('/api/v1/funnels', [ApiController::class, 'funnels']);
$router->get('/api/v1/submissions', [ApiController::class, 'submissions']);

// Protected Admin Routes Group
$router->group([AuthMiddleware::class], function (Router $router) {
    // Switch tenant
    $router->post('/admin/switch-workspace', [AuthController::class, 'switchWorkspace']);

    // Platform Administration (Super Admin Exclusive)
    $router->group([PlatformAdminMiddleware::class], function (Router $router) {
        // Overview Dashboard
        $router->get('/admin/platform', [PlatformAdminController::class, 'index']);
        
        // Customers & Users Management
        $router->get('/admin/platform/customers', [PlatformAdminController::class, 'customers']);
        $router->post('/admin/platform/tenants/{id}/toggle', [PlatformAdminController::class, 'toggleTenantStatus'], [CsrfMiddleware::class]);
        $router->post('/admin/platform/tenants/{id}/assign-plan', [PlatformAdminController::class, 'assignPlan'], [CsrfMiddleware::class]);
        $router->post('/admin/platform/users/{id}/toggle-status', [PlatformAdminController::class, 'toggleUserStatus'], [CsrfMiddleware::class]);
        
        // Plans Management (Make plans for users)
        $router->get('/admin/platform/plans', [PlatformAdminController::class, 'plans']);
        $router->post('/admin/platform/plans/create', [PlatformAdminController::class, 'createPlan'], [CsrfMiddleware::class]);
        $router->post('/admin/platform/plans/{id}/update', [PlatformAdminController::class, 'updatePlan'], [CsrfMiddleware::class]);
        $router->post('/admin/platform/plans/{id}/delete', [PlatformAdminController::class, 'deletePlan'], [CsrfMiddleware::class]);
        
        // Payment Gateways Settings (Auto & Manual)
        $router->get('/admin/platform/payment-gateways', [PlatformAdminController::class, 'paymentGateways']);
        $router->post('/admin/platform/payment-gateways', [PlatformAdminController::class, 'updatePaymentGateways'], [CsrfMiddleware::class]);
        
        // Admin Profile (Change email and password)
        $router->get('/admin/platform/profile', [PlatformAdminController::class, 'profile']);
        $router->post('/admin/platform/profile', [PlatformAdminController::class, 'updateProfile'], [CsrfMiddleware::class]);
        $router->post('/admin/platform/profile/password', [PlatformAdminController::class, 'updatePassword'], [CsrfMiddleware::class]);
        
        // Audit Logs
        $router->get('/admin/platform/logs', [PlatformAdminController::class, 'logs']);

        // Legal Pages & Compliance CMS
        $router->get('/admin/platform/legal-pages', [PlatformAdminController::class, 'legalPages']);
        $router->post('/admin/platform/legal-pages/create', [PlatformAdminController::class, 'createLegalPage'], [CsrfMiddleware::class]);
        $router->post('/admin/platform/legal-pages/{id}/update', [PlatformAdminController::class, 'updateLegalPage'], [CsrfMiddleware::class]);
        $router->post('/admin/platform/legal-pages/{id}/delete', [PlatformAdminController::class, 'deleteLegalPage'], [CsrfMiddleware::class]);

        // API, Webhook & Embedding Documentation Hub
        $router->get('/admin/platform/docs', [PlatformAdminController::class, 'docs']);
    });

    // Tenant-Scoped Workspace Routes
    $router->group([TenantMiddleware::class], function (Router $router) {
        // Dashboard
        $router->get('/admin/dashboard', [DashboardController::class, 'index']);

        // Funnels
        $router->get('/admin/funnels', [FunnelController::class, 'index']);
        $router->get('/admin/funnels/create', [FunnelController::class, 'showCreate']);
        $router->post('/admin/funnels/create', [FunnelController::class, 'store'], [CsrfMiddleware::class]);
        $router->get('/admin/funnels/{id}/settings', [FunnelController::class, 'editSettings']);
        $router->post('/admin/funnels/{id}/settings', [FunnelController::class, 'updateSettings'], [CsrfMiddleware::class]);
        $router->post('/admin/funnels/{id}/duplicate', [FunnelController::class, 'duplicate'], [CsrfMiddleware::class]);
        $router->post('/admin/funnels/{id}/publish', [FunnelController::class, 'publish'], [CsrfMiddleware::class]);
        $router->post('/admin/funnels/{id}/unpublish', [FunnelController::class, 'unpublish'], [CsrfMiddleware::class]);
        $router->post('/admin/funnels/{id}/delete', [FunnelController::class, 'delete'], [CsrfMiddleware::class]);

        // Visual Builder Workspace & API
        $router->get('/admin/funnels/{id}/builder', [BuilderController::class, 'index']);
        $router->get('/api/funnels/{id}/graph', [BuilderController::class, 'getGraph']);
        $router->post('/api/funnels/{id}/graph', [BuilderController::class, 'saveGraph']);
        $router->get('/api/funnels/{id}/validate', [BuilderController::class, 'validateGraph']);
        $router->post('/api/funnels/{id}/form-fields', [FunnelController::class, 'saveFormFields']);

        // Submissions & Journey Inspector
        $router->get('/admin/submissions', [SubmissionController::class, 'index']);
        $router->get('/admin/submissions/export', [SubmissionController::class, 'export']);
        $router->get('/admin/submissions/{id}', [SubmissionController::class, 'show']);

        // Analytics
        $router->get('/admin/analytics', [AnalyticsController::class, 'index']);

        // Team Management
        $router->get('/admin/team', [TeamController::class, 'index']);
        $router->post('/admin/team', [TeamController::class, 'store'], [CsrfMiddleware::class]);
        $router->post('/admin/team/{id}/update', [TeamController::class, 'update'], [CsrfMiddleware::class]);
        $router->post('/admin/team/{id}/remove', [TeamController::class, 'remove'], [CsrfMiddleware::class]);

        // Webhooks & Integrations
        $router->get('/admin/webhooks', [WebhookController::class, 'index']);
        $router->post('/admin/webhooks', [WebhookController::class, 'store'], [CsrfMiddleware::class]);
        $router->post('/admin/webhooks/{id}/update', [WebhookController::class, 'update'], [CsrfMiddleware::class]);
        $router->post('/admin/webhooks/{id}/delete', [WebhookController::class, 'delete'], [CsrfMiddleware::class]);
        $router->post('/admin/webhooks/{id}/test', [WebhookController::class, 'test'], [CsrfMiddleware::class]);
        $router->post('/admin/webhooks/deliveries/{id}/retry', [WebhookController::class, 'retryDelivery'], [CsrfMiddleware::class]);

        // Settings
        $router->get('/admin/settings/workspace', [SettingsController::class, 'workspace']);
        $router->post('/admin/settings/workspace', [SettingsController::class, 'updateWorkspace'], [CsrfMiddleware::class]);
        $router->post('/admin/settings/workspace/invite', [SettingsController::class, 'inviteMember'], [CsrfMiddleware::class]);
        $router->post('/admin/settings/workspace/members/{id}/remove', [SettingsController::class, 'removeMember'], [CsrfMiddleware::class]);

        // Profile
        $router->get('/admin/settings/profile', [SettingsController::class, 'profile']);
        $router->post('/admin/settings/profile', [SettingsController::class, 'updateProfile'], [CsrfMiddleware::class]);
    });
});
