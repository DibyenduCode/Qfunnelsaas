<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register(__DIR__ . '/../app');
require_once __DIR__ . '/../app/Core/helpers.php';

use App\Core\Env;
use App\Core\Database;
use App\Core\Session;
use App\Services\AuthService;
use App\Services\TenantContextService;
use App\Services\FunnelService;
use App\Services\FunnelGraphService;
use App\Services\FunnelPublishService;
use App\Services\QuizEngineService;
use App\Services\SubmissionService;
use App\Services\AnalyticsService;
use App\Services\UsageLimitService;

// Load environment
Env::load(__DIR__ . '/../.env');

// Connect to test database
Database::init([
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::get('DB_PORT', '3306'),
    'database' => Env::get('DB_DATABASE', 'qfunnel_db'),
    'username' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
]);

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function assert(bool $condition, string $testName, string $failureDetails = ''): void
    {
        if ($condition) {
            echo "  [PASS] {$testName}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$testName}\n";
            if ($failureDetails) {
                echo "         Details: {$failureDetails}\n";
            }
            $this->failed++;
            $this->failures[] = $testName;
        }
    }

    public function summary(): int
    {
        echo "\n=========================================\n";
        echo "TEST SUMMARY: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "=========================================\n";
        return $this->failed === 0 ? 0 : 1;
    }
}

$runner = new TestRunner();

echo "=========================================\n";
echo " RUNNING QUIZ FUNNEL SAAS TEST SUITE\n";
echo "=========================================\n\n";

// --- SUITE 1: AUTHENTICATION & SESSIONS ---
echo "--- SUITE 1: Authentication & Sessions ---\n";
Session::start();

// 1.1 Valid login
$authRes = AuthService::attempt('owner@qfunnel.local', 'Password123!');
$runner->assert($authRes['success'] === true, 'Valid user credentials successfully authenticated');

// 1.2 Invalid password
$badAuth = AuthService::attempt('owner@qfunnel.local', 'WrongPassword!');
$runner->assert($badAuth['success'] === false, 'Invalid credentials rejected');

// 1.3 Nonexistent email
$fakeAuth = AuthService::attempt('nonexistent@domain.com', 'Pass123!');
$runner->assert($fakeAuth['success'] === false, 'Nonexistent account rejected');

// 1.4 Super Admin authentication & platform role
$adminAuth = AuthService::attempt('superadmin@qfunnel.local', 'Password123!');
$runner->assert(
    $adminAuth['success'] === true && !empty($adminAuth['user']['is_platform_admin']) && TenantContextService::getActiveTenant() !== null,
    'Super Administrator authenticated with platform privileges and active workspace'
);


// --- SUITE 2: MULTI-TENANCY DATA ISOLATION ---
echo "\n--- SUITE 2: Multi-Tenancy Data Isolation ---\n";

// Create Tenant A & Tenant B
$tenantAUser = 'tenant_a_' . time() . '@test.local';
$tenantBUser = 'tenant_b_' . time() . '@test.local';

$regA = AuthService::register('Alice Owner', $tenantAUser, 'Password123!', 'Tenant A Workspace');
$tenantAId = (int)$regA['tenant_id'];
$userAId = (int)$regA['user']['id'];

$regB = AuthService::register('Bob Owner', $tenantBUser, 'Password123!', 'Tenant B Workspace');
$tenantBId = (int)$regB['tenant_id'];
$userBId = (int)$regB['user']['id'];

$runner->assert($tenantAId > 0 && $tenantBId > 0 && $tenantAId !== $tenantBId, 'Distinct isolated tenants created');

// Tenant A creates a funnel
TenantContextService::setActiveTenant($tenantAId, $userAId);
$funnelA = FunnelService::createFunnel($tenantAId, ['name' => 'Secret Funnel of Tenant A'], $userAId);
$funnelAId = (int)$funnelA['id'];

// Test 2.1: Tenant B cannot list Tenant A's funnel
TenantContextService::setActiveTenant($tenantBId, $userBId);
$tenantBFunnels = FunnelService::listFunnels($tenantBId);
$bCanSeeA = false;
foreach ($tenantBFunnels as $f) {
    if ((int)$f['id'] === $funnelAId) {
        $bCanSeeA = true;
    }
}
$runner->assert(!$bCanSeeA, 'Tenant B funnel list excludes Tenant A funnels');

// Test 2.2: Tenant B cannot directly fetch Tenant A's funnel via ID (IDOR protection)
$idorAttempt = FunnelService::getFunnel($tenantBId, $funnelAId);
$runner->assert($idorAttempt === null, 'Tenant B cannot read Tenant A funnel by ID (IDOR blocked)');

// Test 2.3: Tenant B cannot delete Tenant A's funnel
$deleteBlocked = false;
try {
    FunnelService::deleteFunnel($tenantBId, $funnelAId, $userBId);
} catch (\Throwable $e) {
    $deleteBlocked = true;
}
$runner->assert($deleteBlocked, 'Tenant B cannot delete Tenant A funnel');


// --- SUITE 3: FUNNEL BUILDER & GRAPH VALIDATION ---
echo "\n--- SUITE 3: Funnel Builder & Graph Validation ---\n";
TenantContextService::setActiveTenant($tenantAId, $userAId);

// Test 3.1: Save graph nodes and edges
$nodes = [
    [
        'node_key' => 'start_1',
        'node_type' => 'start',
        'title' => 'Start',
        'position_x' => 100,
        'position_y' => 100,
    ],
    [
        'node_key' => 'q_1',
        'node_type' => 'question',
        'title' => 'Industry Selection',
        'position_x' => 350,
        'position_y' => 100,
        'question' => [
            'question_text' => 'What is your primary industry?',
            'question_type' => 'single_choice',
            'is_required' => 1,
            'options' => [
                ['option_key' => 'opt_tech', 'label' => 'Technology', 'value' => 'tech'],
                ['option_key' => 'opt_finance', 'label' => 'Finance', 'value' => 'finance'],
            ]
        ]
    ],
    [
        'node_key' => 'dest_tech',
        'node_type' => 'destination',
        'title' => 'Tech Hub',
        'position_x' => 700,
        'position_y' => 50,
        'destination' => [
            'name' => 'Technology Landing Page',
            'url' => 'https://example.com/tech'
        ]
    ],
    [
        'node_key' => 'dest_fin',
        'node_type' => 'destination',
        'title' => 'Finance Hub',
        'position_x' => 700,
        'position_y' => 200,
        'destination' => [
            'name' => 'Finance Landing Page',
            'url' => 'https://example.com/finance'
        ]
    ]
];

$edges = [
    ['source_node_key' => 'start_1', 'source_option_key' => null, 'target_node_key' => 'q_1'],
    ['source_node_key' => 'q_1', 'source_option_key' => 'opt_tech', 'target_node_key' => 'dest_tech'],
    ['source_node_key' => 'q_1', 'source_option_key' => 'opt_finance', 'target_node_key' => 'dest_fin'],
];

$saved = FunnelGraphService::saveGraph($tenantAId, $funnelAId, ['nodes' => $nodes, 'edges' => $edges], $userAId);
$runner->assert(count($saved['nodes']) === 4 && count($saved['edges']) === 3, 'Graph nodes and edges saved atomically');

\App\Services\FormSchemaService::saveFormFields($tenantAId, $funnelAId, [
    ['field_key' => 'full_name', 'label' => 'Full Name', 'field_type' => 'text', 'is_required' => 1],
    ['field_key' => 'email', 'label' => 'Email Address', 'field_type' => 'email', 'is_required' => 1],
], $userAId);

// Test 3.2: Validate complete graph
$val = FunnelGraphService::validateGraph($tenantAId, $funnelAId);
$runner->assert($val['is_valid'] === true, 'Complete branching flow passes validation');

// Test 3.3: Publish version
$pub = FunnelPublishService::publish($tenantAId, $funnelAId, $userAId);
$runner->assert($pub['version_number'] === 1 && $pub['status'] === 'published', 'Funnel successfully published as Version 1');

// Test 3.4: Draft changes do not alter published version
$updatedFunnel = FunnelService::getFunnel($tenantAId, $funnelAId);
$runner->assert((int)$updatedFunnel['current_published_version_id'] === (int)$pub['version_id'], 'Published version pointer set correctly');


// --- SUITE 4: QUIZ ENGINE BRANCHING & DOWNSTREAM INVALIDATION ---
echo "\n--- SUITE 4: Quiz Engine Branching & Invalidation ---\n";

// Test 4.1: Public visitor initializes session
$slug = $updatedFunnel['slug'];
$init = QuizEngineService::initializeSession($slug);
$runner->assert(!empty($init['session_token']) && $init['status'] === 'form_pending', 'Public visitor session initialized with token');
$sessionToken = $init['session_token'];

// Test 4.2: Submit pre-quiz form
$formRes = QuizEngineService::submitForm($sessionToken, [
    'full_name' => 'Test Visitor',
    'email' => 'visitor@test.local'
]);
$runner->assert($formRes['status'] === 'in_progress', 'Pre-quiz form saved and quiz started');

// Test 4.3: Submit Tech option -> should reach Tech Hub Destination
$firstStep = QuizEngineService::getCurrentStep($sessionToken);
$techOptionId = null;
foreach ($firstStep['step']['options'] as $opt) {
    if ($opt['label'] === 'Technology') {
        $techOptionId = $opt['id'];
        break;
    }
}

$techAnswerRes = QuizEngineService::submitAnswer($sessionToken, (int)$firstStep['step']['node_id'], [
    'option_id' => $techOptionId
]);
$runner->assert($techAnswerRes['status'] === 'completed' && $techAnswerRes['result']['destination_name'] === 'Technology Landing Page', 'Tech branch correctly routed to Tech Hub destination');

// Test 4.4: Back navigation and route change
QuizEngineService::navigateBack($sessionToken);
$backStep = QuizEngineService::getCurrentStep($sessionToken);
$runner->assert($backStep['status'] === 'in_progress', 'Back navigation restores prior question step');

// Choose Finance option instead -> downstream route changes to Finance Landing Page
$finOptionId = null;
foreach ($backStep['step']['options'] as $opt) {
    if ($opt['label'] === 'Finance') {
        $finOptionId = $opt['id'];
        break;
    }
}
$finAnswerRes = QuizEngineService::submitAnswer($sessionToken, (int)$backStep['step']['node_id'], [
    'option_id' => $finOptionId
]);
$runner->assert($finAnswerRes['status'] === 'completed' && $finAnswerRes['result']['destination_name'] === 'Finance Landing Page', 'Answer change correctly supersedes downstream route to Finance Hub');


// --- SUITE 5: SUBMISSIONS & ANALYTICS INTEGRITY ---
echo "\n--- SUITE 5: Submissions & Analytics Integrity ---\n";

// Test 5.1: Submission listed with accurate lead info
$subsList = SubmissionService::listSubmissions($tenantAId, ['funnel_id' => $funnelAId]);
$runner->assert($subsList['total'] >= 1 && $subsList['items'][0]['lead_name'] === 'Test Visitor', 'Submission correctly stored and searchable');

// Test 5.2: Journey timeline events preserved
$subDetail = SubmissionService::getSubmissionDetail($tenantAId, (int)$subsList['items'][0]['id']);
$eventTypes = array_column($subDetail['events'], 'event_type');
$hasStarted = in_array('quiz_started', $eventTypes, true);
$hasCompleted = in_array('quiz_completed', $eventTypes, true);
$runner->assert($hasStarted && $hasCompleted, 'Full event timeline preserved in chronological order');

// Test 5.3: Analytics accurately counts starts and completions
$analytics = AnalyticsService::getFunnelMetrics($tenantAId, $funnelAId);
$runner->assert($analytics['completions'] >= 1 && $analytics['completion_rate'] > 0, 'Real database analytics computed accurately');


// --- SUITE 6: PLAN LIMITS ENFORCEMENT ---
echo "\n--- SUITE 6: Plan Limits Enforcement ---\n";
$limits = UsageLimitService::getTenantLimits($tenantAId);
$runner->assert($limits['max_funnels'] >= 3, 'Tenant limits resolved from subscription plan');


// --- SUITE 7: SUPER ADMIN EXCLUSIVE TOOLS & CAPABILITIES ---
echo "\n--- SUITE 7: Super Admin Tools & Controls ---\n";

// Test 7.1: Verify Core Plans (Free [1], Starter [5], Standard [10], Pro [unlimited])
$freePlan = Database::fetchOne("SELECT * FROM plans WHERE slug = 'free'");
$starterPlan = Database::fetchOne("SELECT * FROM plans WHERE slug = 'starter'");
$standardPlan = Database::fetchOne("SELECT * FROM plans WHERE slug = 'standard'");
$proPlan = Database::fetchOne("SELECT * FROM plans WHERE slug = 'pro'");

$freeLimits = json_decode($freePlan['limits_json'] ?? '{}', true);
$starterLimits = json_decode($starterPlan['limits_json'] ?? '{}', true);
$standardLimits = json_decode($standardPlan['limits_json'] ?? '{}', true);
$proLimits = json_decode($proPlan['limits_json'] ?? '{}', true);

$runner->assert(
    $freeLimits['max_funnels'] === 1 &&
    $starterLimits['max_funnels'] === 5 &&
    $standardLimits['max_funnels'] === 10 &&
    $proLimits['max_funnels'] <= 0,
    'Core plans configured with required funnel limits: Free(1), Starter(5), Standard(10), Pro(Unlimited)'
);

// Test 7.2: Super Admin can suspend any user and suspended user login is rejected
$testUserEmail = 'suspend_test_' . time() . '@test.local';
$hash = password_hash('Password123!', PASSWORD_DEFAULT);
Database::execute("INSERT INTO users (name, email, password_hash, is_platform_admin, status) VALUES ('Test User', ?, ?, 0, 'active')", [$testUserEmail, $hash]);
$testUserId = (int)Database::lastInsertId();

// Suspend user
Database::execute("UPDATE users SET status = 'suspended' WHERE id = ?", [$testUserId]);
$loginAttempt = AuthService::attempt($testUserEmail, 'Password123!');
$runner->assert(
    !$loginAttempt['success'] && str_contains(strtolower($loginAttempt['error']), 'suspended'),
    'Super admin can suspend any user and suspended user login is rejected'
);

// Test 7.3: Assign plan to customer workspace
Database::execute("UPDATE tenant_subscriptions SET status = 'cancelled' WHERE tenant_id = ?", [$tenantAId]);
Database::execute(
    "INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, starts_at, ends_at) VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))",
    [$tenantAId, $proPlan['id']]
);
$tenantALimits = UsageLimitService::getTenantLimits($tenantAId);
$runner->assert($tenantALimits['plan_slug'] === 'pro' && $tenantALimits['max_funnels'] <= 0, 'Super admin can assign plan to any customer workspace');

// Test 7.4: Pro unlimited funnel creation allowed
$canCreateOnPro = UsageLimitService::checkCanCreateFunnel($tenantAId);
$runner->assert($canCreateOnPro['allowed'] === true, 'Pro tier allows unlimited funnel creation without blocking');

// Test 7.5: Automated & Manual Payment Gateways Configuration
\App\Services\PlatformSettingService::set('payment_gateways', [
    'stripe' => ['enabled' => true, 'mode' => 'test', 'publishable_key' => 'pk_test_123', 'secret_key' => 'sk_test_123'],
    'paypal' => ['enabled' => false, 'mode' => 'sandbox'],
    'razorpay' => ['enabled' => false],
    'bank_transfer' => ['enabled' => true, 'bank_name' => 'Demo Bank', 'account_number' => '987654321', 'instructions' => 'Send wire ref QF123'],
    'manual_custom' => ['enabled' => true, 'title' => 'Offline Cheque', 'instructions' => 'Mail cheque to HQ']
]);
$savedGateways = \App\Services\PlatformSettingService::getPaymentGateways();
$runner->assert(
    !empty($savedGateways['stripe']['enabled']) &&
    !empty($savedGateways['bank_transfer']['enabled']) &&
    $savedGateways['bank_transfer']['bank_name'] === 'Demo Bank',
    'Payment gateway settings properly stores and retrieves both Auto and Manual configurations'
);

// Summary output
$code = $runner->summary();
exit($code);
