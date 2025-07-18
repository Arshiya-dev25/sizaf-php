<?php
$metaTitle = "News";
$metaDescription = "News Page.";
$metaKeywords = "IT solutions, software development, consultancy";
$metaImage = "https://yourdomain.com/images/og-governance.jpg";
$canonicalURL = "https://sizaf.com/news.php"; 
session_start();
require_once __DIR__ . '/vendor/autoload.php';
include 'header.php';
include 'functions.php';

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Define sensitive keywords
const SENSITIVE_KEYWORDS = [
    "violence","virgin", "explicit", "vulgar", "controversy", "terror", "drugs",
    "abuse", "offensive", "porn", "adult", "crime", "scandal", "murder", "death"
];

// GNews API Key from .env
$apiKey = $_ENV['GNEWS_API_KEY'] ?? null;
if (!$apiKey) {
    $error_message = "API key not found. Please set GNEWS_API_KEY in your .env file.";
    $newsData = ['articles' => []];
} else {
    // GNews API URL (fetch 10 results)
    $apiUrl = "https://gnews.io/api/v4/search?q=IPT%20OR%20ICT%20OR%20ISP%20OR%20Broadband&in=title&lang=en&category=technology&apikey={$apiKey}&max=10";

    // Fetch news data
    $response = @file_get_contents($apiUrl);
    
    if ($response === false) {
        $error_message = "Error fetching news. Please try again later.";
        $newsData = ['articles' => []];
    } else {
        $newsData = json_decode($response, true);
    }
}

// Check if articles are available in the response
if (empty($newsData['articles'])) {
    $_SESSION['news_articles'] = [];
} else {
    $_SESSION['news_articles'] = $newsData['articles'];
}

// Filter out articles containing sensitive keywords
$seenTitles = [];
$filteredNews = [];

foreach ($newsData['articles'] as $article) {
    $containsSensitive = false;
    foreach (SENSITIVE_KEYWORDS as $keyword) {
        if (
            stripos($article['title'], $keyword) !== false ||
            stripos($article['description'], $keyword) !== false
        ) {
            $containsSensitive = true;
            break;
        }
    }
    if ($containsSensitive || in_array($article['title'], $seenTitles)) {
        continue;
    }

    $seenTitles[] = $article['title'];
    $filteredNews[] = $article;
}
?>

<main class="max-w-[1460px] container mx-auto">
    <!-- Hero Section -->
    <div class=" mx-auto">
        <div class="relative rounded-b-3xl overflow-hidden mb-16 shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 to-purple-900/90 z-10"></div>
            <img 
                src="./assest/bg.webp" 
                alt="Latest tech insights" 
                width="1200" 
                height="400" 
                class="w-full h-[400px] object-cover"
                loading="lazy"
                decoding="async"
            />
            <div class="absolute inset-0 z-20 flex flex-col justify-center px-8 md:px-16">
                <span class="mb-4 bg-white/10 backdrop-blur-sm text-white border-white/20 w-fit px-3 py-1 rounded-full text-sm font-medium">
                    SIZAF Newsroom
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4">
                    Stay informed with the latest
                    <span class="bg-gradient-to-r from-pink-400 to-purple-400 bg-clip-text text-transparent">
                        breaking news
                    </span>
                </h1>
                <p class="text-xl text-white/90 mb-8 max-w-2xl">
                    Real-time updates from around the world covering politics, business, tech, science, and more.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="global-network.php" class="bg-pink-500 hover:bg-pink-600 text-white px-8 py-4 text-lg rounded-lg font-medium text-center transition-colors">
                        PAY PER USE RM 1.99
                    </a>
                    <a href="global-network.php" class="border border-white/30 text-white hover:bg-white/10 px-8 py-4 text-lg rounded-lg font-medium text-center transition-colors backdrop-blur-sm flex items-center justify-center">
                        Browse Plans
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col lg:flex-row">
        <?php include 'mini-sidebar.php'; ?>

        <!-- News Section -->
        <div class="flex-1 lg:max-h-[80vh] lg:overflow-y-auto p-4 sm:p-6">
            <header class="mb-8">
                <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4 text-center">NEWS</h1>
                <p class="text-lg text-slate-600 text-center max-w-3xl mx-auto">
                    Discover the latest announcements, product launches, tech updates, and company milestones. Stay informed with breaking news and expert insights from the SIZAF team.
                </p>
            </header>
            <?php if (!empty($filteredNews)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($filteredNews as $index => $news): ?>
                        <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col h-full">
                            <?php if (!empty($news['image'])): ?>
                                <img src="<?= $news['image'] ?>" alt="<?= $index ?>" class="w-full h-48 object-cover" loading="lazy">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">No Image</div>
                            <?php endif; ?>
                            <div class="p-4 flex flex-col h-full">
                                <h3 class="text-lg md:text-xl font-semibold mb-2"><?= htmlspecialchars($news['title']) ?></h3>
                                <p class="text-sm text-gray-500 mb-2"><?= date('F j, Y', strtotime($news['publishedAt'])) ?></p>
                                <p class="text-gray-600 mb-4"><?= substr(strip_tags($news['description']), 0, 100) ?>...</p>
                                <?php 
                                    $slug = slugify($news['title']); 
                                    $news['slug'] = $slug;
                                    $_SESSION['news_articles'][$slug] = $news; 
                                ?>
                                <a href="<?= $slug ?>" class="bg-primary-gradient text-white w-28 py-2 rounded mt-auto text-center">Read More</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-red-600 py-8 flex-1 h-full">
                    <p>No news articles available at the moment. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>