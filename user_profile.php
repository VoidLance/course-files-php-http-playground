<?php
declare(strict_types=1);
session_start();

#
# User Profile System
# Demonstrates conditionals: if-else, if-elseif-else, and switch.
#

# Escape output for safe HTML rendering.
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

# Return the current hour in 24-hour format (0-23).
function getCurrentHour(): int
{
    return (int) date('G');
}

# Determine greeting based on hour using if-else logic.
function getGreetingByHour(int $hour): string
{
    if ($hour < 0 || $hour > 23) {
        throw new InvalidArgumentException('Hour must be between 0 and 23.');
    }

    if ($hour >= 5 && $hour < 12) {
        return 'Good morning';
    } elseif ($hour >= 12 && $hour < 18) {
        return 'Good afternoon';
    } elseif ($hour >= 18 && $hour < 22) {
        return 'Good evening';
    }

    return 'Good night';
}

# Return an age-specific message using if-elseif-else logic.
function getAgeMessage(int $age): string
{
    if ($age < 0) {
        throw new InvalidArgumentException('Age cannot be negative.');
    }

    if ($age < 13) {
        return 'Minor: account access is limited for child safety.';
    } elseif ($age < 18) {
        return 'Teenager: some sections may require supervision.';
    } elseif ($age < 65) {
        return 'Adult: full access granted.';
    }

    return 'Senior: thank you for being with us. Enjoy your experience.';
}

#
# Classify the user by login count using switch.
# 0 = New User, 1-5 = Beginner, 6-20 = Regular User, 21+ = Expert User.
#
function getUserType(int $loginCount): string
{
    if ($loginCount < 0) {
        throw new InvalidArgumentException('Login count cannot be negative.');
    }

    switch (true) {
        case $loginCount === 0:
            return 'New User';
        case $loginCount >= 1 && $loginCount <= 5:
            return 'Beginner';
        case $loginCount >= 6 && $loginCount <= 20:
            return 'Regular User';
        default:
            return 'Expert User';
    }
}

# Validate input data and collect errors for basic error handling.
function validateUserData(string $name, int $age, int $loginCount): array
{
    $errors = [];

    if (trim($name) === '') {
        $errors[] = 'Name cannot be empty.';
    }

    if ($age < 0) {
        $errors[] = 'Age cannot be negative.';
    }

    if ($loginCount < 0) {
        $errors[] = 'Login count cannot be negative.';
    }

    return $errors;
}

# Validate additional profile attributes used by optional project extensions.
function validateAdditionalData(string $membershipTier, string $preferredLanguage, int $completedProjects): array
{
    $errors = [];
    $allowedTiers = ['Basic', 'Pro', 'VIP'];

    if (!in_array($membershipTier, $allowedTiers, true)) {
        $errors[] = 'Membership tier must be Basic, Pro, or VIP.';
    }

    if (trim($preferredLanguage) === '') {
        $errors[] = 'Preferred language cannot be empty.';
    }

    if ($completedProjects < 0) {
        $errors[] = 'Completed projects cannot be negative.';
    }

    return $errors;
}

# Return an additional message based on user type.
function getMotivationByUserType(string $userType, int $loginCount): string
{
    if ($userType === 'Expert User') {
        return 'Congratulations! You unlocked expert status and advanced features.';
    }

    $remaining = max(0, 21 - $loginCount);

    if ($userType === 'Regular User') {
        return "Great consistency. Only {$remaining} more logins to become an Expert User.";
    }

    if ($userType === 'Beginner') {
        return "You are making progress. Keep going: {$remaining} more logins to reach Expert User.";
    }

    return 'Welcome aboard. Start exploring to level up your profile.';
}

# Return a message based on membership level using switch.
function getMembershipMessage(string $membershipTier): string
{
    switch ($membershipTier) {
        case 'VIP':
            return 'VIP: priority support and premium features enabled.';
        case 'Pro':
            return 'Pro: advanced tools and analytics are available.';
        case 'Basic':
            return 'Basic: upgrade anytime to unlock more features.';
        default:
            return 'Unknown membership tier.';
    }
}

# Return a language-specific onboarding hint.
function getLanguageHint(string $preferredLanguage): string
{
    if (strcasecmp($preferredLanguage, 'English') === 0) {
        return 'English content is fully available.';
    } elseif (strcasecmp($preferredLanguage, 'Spanish') === 0) {
        return 'Spanish localization is available for core sections.';
    } elseif (strcasecmp($preferredLanguage, 'French') === 0) {
        return 'French localization is available for most pages.';
    }

    return 'Some translations may be limited for your selected language.';
}

# Return a progress message based on completed projects.
function getProjectMilestoneMessage(int $completedProjects): string
{
    if ($completedProjects >= 20) {
        return 'Milestone reached: you are a project veteran.';
    } elseif ($completedProjects >= 10) {
        return 'Strong progress: you are building serious momentum.';
    } elseif ($completedProjects >= 1) {
        return 'Nice start: each project is sharpening your skills.';
    }

    return 'Begin your first project to start building momentum.';
}

# Optional extension: visualize progress toward Expert User.
function getProgressBar(int $loginCount): string
{
    $target = 21;
    $progress = min($loginCount, $target);
    $percent = (int) round(($progress / $target) * 100);

    return "<div style='width:280px;border:1px solid #999;border-radius:6px;overflow:hidden;'>"
        . "<div style='width:{$percent}%;background:#2e7d32;color:#fff;padding:4px 8px;font-size:12px;'>"
        . e((string) $percent) . "% to Expert"
        . '</div></div>';
}

# Render a single user profile card.
function renderUserProfile(string $name, int $age, int $loginCount, array $attributes = []): void
{
    $errors = validateUserData($name, $age, $loginCount);

    $membershipTier = (string) ($attributes['membershipTier'] ?? 'Basic');
    $preferredLanguage = (string) ($attributes['preferredLanguage'] ?? 'English');
    $completedProjects = (int) ($attributes['completedProjects'] ?? 0);

    $errors = array_merge(
        $errors,
        validateAdditionalData($membershipTier, $preferredLanguage, $completedProjects)
    );

    if ($errors !== []) {
        echo '<h2>Input Error</h2>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . e($error) . '</li>';
        }
        echo '</ul>';
        return;
    }

    $hour = getCurrentHour();
    $currentTime = date('h:i A');
    $greeting = getGreetingByHour($hour);
    $ageMessage = getAgeMessage($age);
    $userType = getUserType($loginCount);
    $bonusMessage = getMotivationByUserType($userType, $loginCount);
    $membershipMessage = getMembershipMessage($membershipTier);
    $languageHint = getLanguageHint($preferredLanguage);
    $projectMilestone = getProjectMilestoneMessage($completedProjects);

    echo '<h1>User Profile</h1>';
    echo '<p>' . e($greeting) . ', ' . e($name) . '!</p>';
    echo '<p><strong>Current Time:</strong> ' . e($currentTime) . ' (hour: ' . e((string) $hour) . ')</p>';
    echo '<p><strong>Name:</strong> ' . e($name) . '</p>';
    echo '<p><strong>Age:</strong> ' . e((string) $age) . '</p>';
    echo '<p><strong>Age Message:</strong> ' . e($ageMessage) . '</p>';
    echo '<p><strong>User Type:</strong> ' . e($userType) . '</p>';
    echo '<p><strong>Login Count:</strong> ' . e((string) $loginCount) . '</p>';
    echo '<p><strong>Membership Tier:</strong> ' . e($membershipTier) . '</p>';
    echo '<p><strong>Preferred Language:</strong> ' . e($preferredLanguage) . '</p>';
    echo '<p><strong>Completed Projects:</strong> ' . e((string) $completedProjects) . '</p>';
    echo '<p><strong>Extra:</strong> ' . e($bonusMessage) . '</p>';
    echo '<p><strong>Membership Message:</strong> ' . e($membershipMessage) . '</p>';
    echo '<p><strong>Language Hint:</strong> ' . e($languageHint) . '</p>';
    echo '<p><strong>Project Milestone:</strong> ' . e($projectMilestone) . '</p>';
    echo getProgressBar($loginCount);
}

#
# Test helper for checking all conditional paths with simulated users.
# This demonstrates minors, teenagers, adults, seniors, and invalid data.
#
function renderTestCases(): void
{
    $testUsers = [
        ['name' => 'Alex', 'age' => 10, 'loginCount' => 0],
        ['name' => 'Rina', 'age' => 16, 'loginCount' => 3],
        ['name' => 'Sam', 'age' => 30, 'loginCount' => 12],
        ['name' => 'Pat', 'age' => 70, 'loginCount' => 24],
        ['name' => 'Invalid User', 'age' => -1, 'loginCount' => -5],
    ];

    echo '<hr><h2>Test Scenarios</h2>';
    foreach ($testUsers as $case) {
        echo '<div style="margin-bottom:14px;padding:10px;border:1px solid #ccc;border-radius:6px;">';
        echo '<p><strong>Case:</strong> ' . e($case['name']) . '</p>';
        renderUserProfile($case['name'], $case['age'], $case['loginCount']);
        echo '</div>';
    }
}

# Simulated user data (in a real system this would come from a database).
$userName = 'John Doe';
$userAge = 25;
$defaultLoginCount = 10;
$userAttributes = [
    'membershipTier' => 'Pro',
    'preferredLanguage' => 'English',
    'completedProjects' => 8,
];

#
# Simple login simulation:
# - action=login: increment login count
# - action=reset: reset login count to default
#
if (!isset($_SESSION['loginCount'])) {
    $_SESSION['loginCount'] = $defaultLoginCount;
}

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';

if ($action === 'login') {
    $_SESSION['loginCount']++;
} elseif ($action === 'reset') {
    $_SESSION['loginCount'] = $defaultLoginCount;
}

$loginCount = (int) $_SESSION['loginCount'];

# Main execution
echo '<div style="margin:12px 0;">';
echo '<a href="?action=login" style="display:inline-block;margin-right:8px;padding:6px 10px;border:1px solid #333;border-radius:4px;text-decoration:none;">Simulate Login</a>';
echo '<a href="?action=reset" style="display:inline-block;padding:6px 10px;border:1px solid #333;border-radius:4px;text-decoration:none;">Reset Logins</a>';
echo '</div>';

renderUserProfile($userName, $userAge, $loginCount, $userAttributes);

# Requirement #10: run through different simulated users.
renderTestCases();
?>