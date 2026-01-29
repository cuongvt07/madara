<?php
/**
 * The Template for Completed Manga Item layout
 * Based on content-1.php but displays modified date instead of chapter list.
 */

$wp_manga_functions = madara_get_global_wp_manga_functions();
?>

<?php if (has_post_thumbnail()) { ?>
    <div class="popular-img widget-thumbnail c-image-hover">
        <a title="<?php echo esc_attr(get_the_title()); ?>" href="<?php echo esc_url(get_the_permalink()); ?>">
            <?php
            // Using 'medium' size as established in content-1.php
            echo madara_thumbnail('medium');
            ?>
        </a>
    </div>
<?php } ?>

<div class="popular-content">
    <div class="post-title font-title">
        <h3 class="h5">
            <a title="<?php echo esc_attr(get_the_title()); ?>" href="<?php echo esc_url(get_the_permalink()); ?>">
                <?php echo esc_html(get_the_title()); ?>
            </a>
        </h3>
    </div>

    <div class="list-chapter">
        <span class="chapter-item">
            <?php
            global $wp_manga_chapter;
            $latest_chapters = $wp_manga_chapter->get_latest_chapters(get_the_ID(), null, 1);
            if ($latest_chapters && !empty($latest_chapters)) {
                $chapter = $latest_chapters[0];
                global $wp_manga_functions;
                $manga_reading_style = $wp_manga_functions->get_reading_style(get_current_user_id(), get_the_ID());
                $chapter_url = $wp_manga_functions->build_chapter_url(get_the_ID(), $chapter, $manga_reading_style);

                echo '<span class="chapter font-meta">';
                echo '<a href="' . esc_url($chapter_url) . '" class="btn-link"> ' . esc_html($chapter['chapter_name']) . ' </a>';
                echo '</span>';
            }
            ?>
            <span class="post-on font-meta">
                <?php echo get_the_modified_date('d/m/Y'); ?>
            </span>
        </span>
    </div>

</div>