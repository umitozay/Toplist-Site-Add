<?php
$title = '';
$description = '';
$favicon = '';
$showTitleInput = false;
$showDescriptionInput = false;
$showFaviconURLInput = false;

if (isset($_POST['url'])) {
    $url = $_POST['url'];

    $url = rtrim($url, '/');

    // Add http:// or https:// if missing
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }

    // Fetch the website content
    $htmlContent = @file_get_contents($url);

    if ($htmlContent !== false) {
        preg_match("/<title>(.*?)<\/title>/i", $htmlContent, $titleMatch);
        $title = $titleMatch[1] ?? '';

        preg_match("/<meta name=\"description\" content=\"(.*?)\"/i", $htmlContent, $descriptionMatch);
        $description = $descriptionMatch[1] ?? '';

        // If meta description is not found, use og:description
        if (empty($description)) {
            preg_match("/<meta property=\"og:description\" content=\"(.*?)\"/i", $htmlContent, $ogDescriptionMatch);
            $description = $ogDescriptionMatch[1] ?? '';
        }
        if (empty($description)) {
            $showDescriptionInput = true;
        }

        $favicon = findFavicon($htmlContent, $url);
        if (empty($favicon)) {
            $showFaviconURLInput = true;
        }
    } else {
        $showTitleInput = true;
        $showDescriptionInput = true;
        $showFaviconURLInput = true;
    }
}

function findFavicon($htmlContent, $url) {
    $patterns = [
        '/<link[^>]*rel=["\'](?:icon|shortcut icon|apple-touch-icon)[^>]*href=["\']([^"\']+)/i',
    ];

    foreach ($patterns as $pattern) {
        preg_match($pattern, $htmlContent, $matches);
        if (!empty($matches[1])) {
            return (strpos($matches[1], 'http') === 0) ? $matches[1] : $url . '/' . ltrim($matches[1], '/');
        }
    }

    return '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Website Directory</title>
    <!-- Add your CSS styling here -->
</head>
<body>
    <h1>Website Directory</h1>
    <form action="" method="post">
        <input type="text" name="url" placeholder="Enter website URL">
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($title) || !empty($description) || !empty($favicon)) : ?>
        <h2>Website Information</h2>
        <?php if (!empty($title)) : ?>
            <p>Title: <?php echo $title; ?></p>
        <?php elseif ($showTitleInput) : ?>
            <form action="" method="post">
                <label for="manualTitle">Custom Title:</label>
                <input type="text" id="manualTitle" name="manualTitle" placeholder="Enter custom title">
                <br>
            </form>
        <?php endif; ?>

        <?php if (!empty($description)) : ?>
            <p>Description: <?php echo $description; ?></p>
        <?php elseif ($showDescriptionInput) : ?>
            <form action="" method="post">
                <label for="manualDescription">Custom Description:</label>
                <input type="text" id="manualDescription" name="manualDescription" placeholder="Enter custom description">
                <br>
            </form>
        <?php endif; ?>

        <?php if (!empty($favicon)) : ?>
            <h2>Favicon</h2>
            <img src="<?php echo $favicon; ?>" alt="Website Favicon" />
        <?php elseif ($showFaviconURLInput) : ?>
            <form action="" method="post">
                <label for="manualFavicon">Custom Favicon URL:</label>
                <input type="text" id="manualFavicon" name="manualFavicon" placeholder="Enter custom favicon URL">
                <br>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
