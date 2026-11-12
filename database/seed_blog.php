<?php
/**
 * WRC - Blog schema and starter content.
 *
 * The public blog reuses news_articles with category = 'blog', so blog posts
 * already flow into the existing news ticker, news listings and admin
 * permissions. Two additive columns give editors the extra controls a blog
 * needs:
 *
 *   news_articles.tags         comma-separated topic labels
 *   news_articles.is_featured  pins one post to the top of the blog
 *
 * blog_settings holds the editable hero and introduction copy for the blog
 * landing page. Nothing here overwrites dashboard work: the table is created
 * once, missing columns are added once, and starter posts are written only
 * when the seed marker has not been set.
 *
 * Standalone:  php database/seed_blog.php
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

if ( !function_exists( 'wrcBlogColumnExists' ) ) {
    function wrcBlogColumnExists( PDO $pdo, string $table, string $column ): bool {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $statement->execute( [ $table, $column ] );
        return (int) $statement->fetchColumn() > 0;
    }
}

if ( !function_exists( 'wrcBlogSetting' ) ) {
    function wrcBlogSetting( PDO $pdo, string $key, string $default = '' ): string {
        try {
            $statement = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1' );
            $statement->execute( [ $key ] );
            $value = $statement->fetchColumn();
            return $value === false || $value === null ? $default : (string) $value;
        } catch ( Throwable $error ) {
            return $default;
        }
    }
}

/**
 * Applies the blog schema. Safe to call directly from the deployment importer.
 *
 * @return array<string,int> Counters for the deployment log.
 */
function wrcSetupBlog( PDO $pdo ): array {
    $result = [
        'columns' => 0,
        'tables'  => 0,
        'posts'   => 0,
        'settings' => 0,
    ];

    if ( !wrcBlogColumnExists( $pdo, 'news_articles', 'tags' ) ) {
        $pdo->exec( 'ALTER TABLE news_articles ADD COLUMN tags VARCHAR(500) NULL AFTER excerpt' );
        $result['columns']++;
    }

    if ( !wrcBlogColumnExists( $pdo, 'news_articles', 'is_featured' ) ) {
        $pdo->exec( 'ALTER TABLE news_articles ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER is_published' );
        $result['columns']++;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS blog_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            hero_badge VARCHAR(120) NOT NULL DEFAULT 'Insights & Field Notes',
            hero_title VARCHAR(255) NOT NULL DEFAULT 'Stories and ideas from the field',
            hero_subtitle TEXT NULL,
            hero_image VARCHAR(500) NOT NULL DEFAULT 'assets/images/hero/news-hero.jpg',
            intro_eyebrow VARCHAR(120) NOT NULL DEFAULT 'The WRC Blog',
            intro_title VARCHAR(255) NOT NULL DEFAULT 'What we are learning, as we learn it',
            intro_text TEXT NULL,
            cta_text VARCHAR(120) NOT NULL DEFAULT 'Partner with WRC',
            cta_link VARCHAR(255) NOT NULL DEFAULT 'get-involved/contribute-with-us.php',
            posts_per_page TINYINT UNSIGNED NOT NULL DEFAULT 6,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $result['tables']++;

    $settingCount = (int) $pdo->query( 'SELECT COUNT(*) FROM blog_settings' )->fetchColumn();
    if ( $settingCount === 0 ) {
        $pdo->exec(
            "INSERT INTO blog_settings
                (hero_badge, hero_title, hero_subtitle, hero_image, intro_eyebrow, intro_title, intro_text, cta_text, cta_link, posts_per_page)
             VALUES
                ('Insights & Field Notes',
                 'Stories and ideas from the field',
                 'Practical lessons, programme insight and human stories from WRC teams working across Pakistan.',
                 'assets/images/hero/news-hero.jpg',
                 'The WRC Blog',
                 'What we are learning, as we learn it',
                 'Long-form reflections from our programme, monitoring and emergency teams. Written for partners, practitioners and anyone who wants to understand how lasting change is built.',
                 'Partner with WRC',
                 'get-involved/contribute-with-us.php',
                 6)"
        );
        $result['settings']++;
    }

    // Starter posts are written once. The marker keeps a deployment from
    // re-creating a post an editor deliberately deleted later.
    $marker = wrcBlogSetting( $pdo, 'blog_seed_content_v1' );
    if ( $marker === '' ) {
        $posts = [
            [
                'slug'         => 'community-led-wash-planning',
                'title'        => 'The Quiet Power of Community-Led WASH Planning',
                'author'       => 'WRC WASH Team',
                'published_at' => '2026-10-28',
                'featured_image' => 'assets/images/projects/buner-wash.jpeg',
                'tags'         => 'WASH,Community,Sustainability',
                'is_featured'  => 1,
                'excerpt'      => 'A water point is only as strong as the committee that keeps it running. Here is how WRC builds local ownership into every WASH project.',
                'content'      => <<<'HTML'
<p>In the villages where WRC works, a hand pump is never just a piece of infrastructure. It is the place where children wash before school, where mothers collect water before the heat arrives, and where older residents exchange news at the end of the day. When it stops working, the whole rhythm of a community changes.</p>

<p>That is why WASH planning cannot begin with a pump. It has to begin with the people who will still be there long after the project team has moved on.</p>

<h2>Start with the people who will stay</h2>

<p>Before a site is selected, our teams sit with village elders, women's groups and local health workers to understand how water is used, who controls it and what has failed before. A conversation that begins with "what do you need?" often ends with a better answer than one that begins with a pre-drawn design.</p>

<p>This is not only a matter of courtesy. A water point that ignores local habits is a water point that will be abandoned. When the community helps choose the location, contributes to the site preparation and nominates the caretakers, it becomes something they own rather than something that was delivered to them.</p>

<h2>Design for the day after handover</h2>

<p>Handover is not the finish line. It is the first day of the system's real life. Every WRC WASH project therefore includes a practical package of support:</p>

<ul>
<li><strong>A trained water management committee</strong> with defined roles, a simple record book and a bank or cash box for minor repairs.</li>
<li><strong>A maintenance plan</strong> that names the local mechanic, lists common faults and sets a realistic household contribution.</li>
<li><strong>Water-quality monitoring</strong> linked to the nearest health facility, so a problem is reported before it becomes an outbreak.</li>
<li><strong>A clean-up and drainage routine</strong> that keeps the area safe during the rainy season.</li>
</ul>

<blockquote>The test of a good WASH project is not how it looks on the day it opens. It is whether the water is still flowing, and still safe, two or three years later.</blockquote>

<h2>Make the committee a real institution</h2>

<p>A committee on paper is not the same as a committee that functions. In several communities we found that the same two people were doing all the work while the rest of the membership had never seen the maintenance fund. The fix was not a new pump but a new habit: short monthly meetings with a public ledger and a standing invitation to the women who use the water most.</p>

<p>We are also learning to bring local government into the process earlier. A hand pump may be community-managed, but the health inspector, the union council and the district water authority still have a role when something goes wrong. A simple contact sheet, agreed before installation, can save weeks of delay later.</p>

<h2>What we are learning</h2>

<p>Community ownership takes time, and it is tempting to compress that time to meet a project calendar. But the pressure to move quickly is exactly what produces the failures that cost more later. In our experience, three things make the biggest difference:</p>

<ol>
<li>Train more people than the minimum, and include women in technical roles, not only in meeting attendance.</li>
<li>Keep the maintenance fund small, transparent and locally held, so it can actually be used.</li>
<li>Return for follow-up visits after handover, and ask the committee what has broken rather than only checking what was built.</li>
</ol>

<p>A water point is a long-term promise. The quiet work of planning with a community — listening, adjusting, training and coming back — is what keeps that promise alive.</p>
HTML,
            ],
            [
                'slug'         => 'what-1046208-lives-taught-us-about-impact',
                'title'        => 'What 1,046,208 Lives Taught Us About Measuring Impact',
                'author'       => 'WRC MEAL Unit',
                'published_at' => '2026-10-14',
                'featured_image' => 'assets/images/projects/community-health.jpg',
                'tags'         => 'Impact,Monitoring,Accountability',
                'is_featured'  => 0,
                'excerpt'      => 'Numbers matter, but they only tell the truth when the people behind them are counted with care. Reflections from WRC monitoring and evaluation work.',
                'content'      => <<<'HTML'
<p>When an organisation reports that it has reached more than one million people, the first reaction is usually the number itself. The harder and more important question is what the number means: who was counted, how, and what changed for them.</p>

<p>At WRC, the headline figure of 1,046,208 beneficiaries is not a trophy. It is a responsibility. Every row behind that total represents a household that received a service, joined a training, visited a camp or took part in a community programme. The quality of the total depends entirely on the quality of the counting.</p>

<h2>Count people, not activities</h2>

<p>It is easy to count the number of kits distributed or the number of sessions held. It is harder to count how many distinct people actually received something useful, especially when a family attends more than one activity or when a community is served by several partners.</p>

<p>Our teams now use a simple rule: an activity is not evidence of reach. A person is. That has pushed us to improve attendance records, remove duplicate entries and separate participants from volunteers, staff and local officials. It has also made our reporting less exciting in the short term — and more honest.</p>

<h2>Make verification normal</h2>

<p>Monitoring is often treated as a separate exercise that happens at the end of a project. We are trying to make it a routine part of delivery. Field officers spot-check records. Community representatives are asked whether the reported activity actually took place. Photos and attendance sheets are reviewed together rather than filed separately.</p>

<p>When a discrepancy appears, the goal is not to punish the field team. It is to understand what made accurate reporting difficult in the first place. Sometimes the answer is a confusing form. Sometimes it is a long walk between sites. Sometimes it is pressure to report a round number. Fixing the system is almost always more useful than blaming the person.</p>

<h2>Report what did not work</h2>

<p>A credible impact report includes the activities that underperformed, the assumptions that turned out to be wrong and the communities that were harder to reach than expected. That information is uncomfortable, but it is also the most useful thing a partner can read.</p>

<blockquote>Impact is not the distance between a plan and a press release. It is the distance between a plan and a changed life, measured as carefully as we can.</blockquote>

<p>This is why our project reviews now include a short "what we would do differently" section. It travels with the numbers, so the next team does not have to learn the same lesson from scratch.</p>

<h2>Keep the person visible</h2>

<p>Statistics make scale manageable, but they can also flatten experience. A beneficiary total cannot show the woman who walked two hours to a health camp, or the father who used a food package to keep his children in school for another month.</p>

<p>We therefore pair quantitative reporting with short, consented case notes from the field. The numbers show how many. The stories remind us why the count matters. Held together, they give a fuller picture than either can provide alone.</p>

<p>One million and forty-six thousand two hundred and eight is a milestone worth acknowledging. It is not a finish line. The next stage of our measurement work is to understand not only how many people we reached, but how many were reached well, and what changed for them after we left.</p>
HTML,
            ],
            [
                'slug'         => 'skills-that-outlast-a-project',
                'title'        => 'Beyond the Ribbon: Building Skills That Outlast a Project',
                'author'       => 'WRC Programmes Team',
                'published_at' => '2026-09-30',
                'featured_image' => 'assets/images/projects/education-children.jpg',
                'tags'         => 'Skills,Livelihoods,Partnership',
                'is_featured'  => 0,
                'excerpt'      => 'Vocational training can change a household’s trajectory — if the skills, tools and markets line up after the ceremony ends.',
                'content'      => <<<'HTML'
<p>A skills programme is easy to photograph. There is a classroom, a certificate, a row of smiling graduates and a ribbon. The more difficult question is what happens six months later, when the room is quiet and the graduate has to turn a new skill into an income.</p>

<p>WRC's vocational training work with partners across Khyber Pakhtunkhwa and Islamabad has taught us that the ceremony is the least important part of the journey. What matters is whether the training is connected to a real market, whether the tools are available and whether the graduate has someone to call when the first order goes wrong.</p>

<h2>Choose the skill with the market, not for the photograph</h2>

<p>Too many training programmes are designed around what a donor wants to fund or what a training centre already teaches. We start instead with a simple local question: who is already earning from this trade, and is there room for one more?</p>

<p>That means talking to shopkeepers, workshop owners, employers and women's groups before the course is advertised. It also means being willing to walk away from a popular course if the local market is already saturated.</p>

<h2>Train for the whole job</h2>

<p>A tailor needs more than stitching. A solar technician needs more than wiring. A small business needs a way to price work, keep records, handle a difficult customer and save for replacement parts. Our stronger programmes now include these practical business skills alongside the technical ones.</p>

<ul>
<li><strong>Start-up support:</strong> a basic tool kit or a small grant, agreed with the trainee rather than chosen for them.</li>
<li><strong>Mentoring:</strong> a local practitioner who can answer questions in the first months after training.</li>
<li><strong>Market linkage:</strong> an introduction to a buyer, a cooperative or a workshop that can offer piecework.</li>
<li><strong>Follow-up:</strong> at least one visit after the course ends, when the real problems have started to appear.</li>
</ul>

<blockquote>A certificate proves attendance. A first repeat customer proves that the training worked.</blockquote>

<h2>Make room for women to stay</h2>

<p>For women in conservative communities, the barrier is rarely interest. It is travel, childcare, permission and the risk of being judged for working outside the home. Programmes that ignore these realities may reach women on paper while losing them in practice.</p>

<p>We have seen better results when training is close to home, when timings are agreed with families, and when women are linked to markets they can access without long journeys. Flexible arrangements are not a concession. They are part of the design.</p>

<h2>A handover that means something</h2>

<p>The strongest programmes leave behind more than graduates. They leave behind a trainer who can teach the next group, a workshop that can host apprentices, and a local association that can speak for the trade. That is slower to build than a one-off course, but it is far more likely to survive the end of the funding.</p>

<p>Success is not the number of certificates issued. It is the graduate who is still using the skill a year later, and who can explain what they would do differently next time.</p>
HTML,
            ],
            [
                'slug'         => 'prepared-before-the-flood',
                'title'        => 'Prepared Before the Flood: Lessons in Emergency Readiness',
                'author'       => 'WRC Emergency Response Team',
                'published_at' => '2026-09-12',
                'featured_image' => 'assets/images/projects/flood-swat.jpg',
                'tags'         => 'Emergency,Resilience,Field Notes',
                'is_featured'  => 0,
                'excerpt'      => 'The best emergency response often begins months before a disaster. Field notes on pre-positioning, local partnerships and dignified aid.',
                'content'      => <<<'HTML'
<p>When floodwater reaches a village, the response is measured in hours. Families need a dry place to sleep, safe water, food that does not require cooking and a way to keep children warm. Every hour spent looking for a supplier or negotiating access is an hour a family spends without help.</p>

<p>That is why emergency readiness is not a separate phase that starts when a disaster is declared. It is the quiet work of preparation that happens long before the first warning.</p>

<h2>Pre-position what people actually need</h2>

<p>A warehouse full of the wrong items is not preparedness. We work from a short, field-tested list: water treatment supplies, hygiene kits, tarpaulins, blankets, cooking sets and ready-to-eat food. The list is reviewed with local teams and adjusted for the season, the region and the needs of women, children and older people.</p>

<p>Stock is stored close enough to reach affected areas quickly but not so close that it is itself at risk from the same flood. The location of every pallet is recorded, and stock levels are checked on a regular schedule rather than only after a disaster.</p>

<h2>Know the road before you need it</h2>

<p>In a flood, the shortest route is often the first to disappear. Our teams map alternative roads, boat crossings and community meeting points in advance. They also identify who can authorise access, where vehicles can be parked safely and how supplies can be moved to the last mile if a truck cannot get through.</p>

<blockquote>Preparedness is the difference between a plan on paper and a delivery that reaches a family on the same day.</blockquote>

<h2>Partner locally, not only formally</h2>

<p>Local partners are the first responders in almost every emergency. They know which mosque or school can be used as a shelter, which shopkeeper has stock, and which families are likely to be missed. Formal agreements matter, but so do the relationships that make a quick decision possible at 2 a.m.</p>

<p>We therefore invest in regular contact with district authorities, community organisations and volunteer networks before a crisis. A short coordination meeting in a quiet month can save days of confusion in a bad one.</p>

<h2>Protect dignity, not just safety</h2>

<p>Emergency aid is often described in terms of quantities: how many kits, how many tents, how many litres. Those numbers are necessary, but they are not enough. A distribution that forces people to queue for hours, separates families or ignores the needs of women and people with disabilities is not a success, even if every item is delivered.</p>

<p>Our field teams now plan distribution around privacy, safety and clear information. They explain what is being given and why, set up separate spaces where needed, and make sure the people least able to push to the front are not left at the back.</p>

<h2>Learn before the next one</h2>

<p>Every response produces lessons: a supplier who could not deliver, a route that closed, a community that was missed. The most useful thing a team can do is write those lessons down while they are fresh and share them with the people who will plan the next response.</p>

<p>Preparedness is not a guarantee that nothing will go wrong. It is a commitment to make the next response faster, fairer and more dignified than the last.</p>
HTML,
            ],
        ];

        $insert = $pdo->prepare(
            'INSERT INTO news_articles
                (slug, title, excerpt, content, category, featured_image, author, published_at, is_published, is_featured, tags)
             VALUES (?, ?, ?, ?, "blog", ?, ?, ?, 1, ?, ?)'
        );

        foreach ( $posts as $post ) {
            $check = $pdo->prepare( 'SELECT COUNT(*) FROM news_articles WHERE slug = ?' );
            $check->execute( [ $post['slug'] ] );
            if ( (int) $check->fetchColumn() > 0 ) {
                continue;
            }

            $insert->execute( [
                $post['slug'],
                $post['title'],
                $post['excerpt'],
                $post['content'],
                $post['featured_image'],
                $post['author'],
                $post['published_at'],
                $post['is_featured'],
                $post['tags'],
            ] );
            $result['posts']++;
        }

        $markerStmt = $pdo->prepare(
            "INSERT INTO site_settings (setting_key, setting_value, setting_group)
             VALUES ('blog_seed_content_v1', '1', 'blog')
             ON DUPLICATE KEY UPDATE setting_value = '1'"
        );
        $markerStmt->execute();
    }

    return $result;
}

$result = wrcSetupBlog( $pdo );

echo 'Blog: ' . $result['columns'] . ' column(s) added, '
    . $result['tables'] . ' settings table ensured, '
    . $result['settings'] . ' settings row seeded, '
    . $result['posts'] . " starter post(s) created.\n";
