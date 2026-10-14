<?php
require_once __DIR__ . '/../../../admin/includes/header-pro.php';

$page_title = 'Blog Posts Management';
/** @var array $blogs */
/** @var string $csrf_token */
/** @var string $message */
/** @var string $message_type */

$blogs = $blogs ?? [];
$csrf_token = $csrf_token ?? '';
$message = $message ?? '';
$message_type = $message_type ?? '';

// Calculate stats
$total_count = count($blogs);
$published_count = 0;
$draft_count = 0;
$total_views = 0;
foreach ($blogs as $b) {
    if (($b['status'] ?? '') === 'published') $published_count++;
    else $draft_count++;
    $total_views += (int)($b['views_count'] ?? 0);
}
?>

<style>
.blog-thumb-preview {
  width: 52px;
  height: 44px;
  border-radius: var(--radius-md);
  object-fit: cover;
  border: 1px solid var(--border-light);
  background: var(--bg-surface-secondary);
}
.blog-title-cell {
  font-weight: 600;
  color: var(--text-primary);
  font-size: var(--text-sm);
  max-width: 320px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.blog-slug-cell {
  font-size: var(--text-xs);
  color: var(--text-secondary);
  font-family: monospace;
  margin-top: 2px;
}
.cat-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 700;
  background: rgba(91, 53, 232, 0.08);
  color: var(--primary);
  text-transform: uppercase;
}
</style>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 style="font-size: var(--text-2xl); font-weight: 700; letter-spacing: -0.02em; color: var(--text-primary);">
      Blog Posts Management
    </h1>
    <p class="text-sm text-muted" style="margin-top: var(--space-1);">
      Create, edit, and organize digital marketing articles published on CherriDM blog.
    </p>
  </div>
  <button class="btn btn-primary" onclick="openBlogModal()">
    <i class="fas fa-plus"></i> Write New Article
  </button>
</div>

<!-- Stats Strip -->
<div class="grid cols-4 mb-6">
  <div class="card p-4">
    <div style="font-size: var(--text-xs); color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Articles</div>
    <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= $total_count ?></div>
  </div>
  <div class="card p-4">
    <div style="font-size: var(--text-xs); color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Published</div>
    <div style="font-size: 24px; font-weight: 800; color: #10B981; margin-top: 4px;"><?= $published_count ?></div>
  </div>
  <div class="card p-4">
    <div style="font-size: var(--text-xs); color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Drafts</div>
    <div style="font-size: 24px; font-weight: 800; color: #64748B; margin-top: 4px;"><?= $draft_count ?></div>
  </div>
  <div class="card p-4">
    <div style="font-size: var(--text-xs); color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Views</div>
    <div style="font-size: 24px; font-weight: 800; color: var(--primary); margin-top: 4px;"><?= number_format($total_views) ?></div>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?= $message_type ?> mb-6">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="table-container">
    <table style="min-width: auto;">
      <thead>
        <tr>
          <th style="width: 70px;">Thumbnail</th>
          <th>Article Title & Slug</th>
          <th>Category</th>
          <th>Date</th>
          <th style="text-align: center;">Views</th>
          <th>Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($blogs as $b): 
          $thumb = '';
          if (!empty($b['image'])) {
            $thumb = (str_starts_with($b['image'], 'http://') || str_starts_with($b['image'], 'https://') || str_starts_with($b['image'], '/')) ? $b['image'] : '../' . $b['image'];
          }
          $pub_date = !empty($b['published_date']) ? date('M d, Y', strtotime($b['published_date'])) : '—';
        ?>
        <tr id="blog-row-<?= $b['id'] ?>">
          <td>
            <?php if ($thumb): ?>
              <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="blog-thumb-preview" onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=120&q=80';">
            <?php else: ?>
              <div class="blog-thumb-preview" style="display: flex; align-items: center; justify-content: center; color: var(--text-tertiary);">
                <i class="fas fa-image"></i>
              </div>
            <?php endif; ?>
          </td>
          <td>
            <div class="blog-title-cell" title="<?= htmlspecialchars($b['title']) ?>"><?= htmlspecialchars($b['title']) ?></div>
            <div class="blog-slug-cell">/blog/<?= htmlspecialchars($b['slug']) ?></div>
          </td>
          <td>
            <span class="cat-badge"><?= htmlspecialchars($b['category'] ?? 'General') ?></span>
          </td>
          <td style="font-size: var(--text-xs); color: var(--text-secondary); white-space: nowrap;">
            <?= htmlspecialchars($pub_date) ?>
          </td>
          <td style="text-align: center; font-weight: 600; color: var(--text-primary); font-size: var(--text-xs);">
            <?= number_format((int)($b['views_count'] ?? 0)) ?>
          </td>
          <td>
            <?php $status_class = ($b['status'] ?? '') === 'published' ? 'badge-success' : 'badge-neutral'; ?>
            <span class="badge-sm <?= $status_class ?>"><?= ucfirst($b['status'] ?? 'draft') ?></span>
          </td>
          <td style="text-align: right;">
            <div style="display: flex; gap: var(--space-2); justify-content: flex-end;">
              <a href="../blog/<?= htmlspecialchars($b['slug']) ?>" target="_blank" class="btn btn-ghost btn-sm" title="View Live Article">
                <i class="fas fa-external-link-alt" style="color: var(--primary);"></i>
              </a>
              <button class="btn btn-ghost btn-sm" onclick="editBlog(<?= htmlspecialchars(json_encode($b)) ?>)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" title="Toggle Publish/Draft">
                  <i class="fas fa-<?= $b['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i>
                </button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this blog article permanently?')">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($blogs)): ?>
        <tr>
          <td colspan="7" style="text-align:center; padding:var(--space-10) var(--space-4); border-bottom: none;">
            <div style="width: 48px; height: 48px; background: var(--bg-surface-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
              <i class="fas fa-newspaper" style="font-size: 20px; color: var(--text-tertiary);"></i>
            </div>
            <h3 style="font-size: var(--text-base); font-weight: 600; color: var(--text-primary); margin-bottom: var(--space-2);">
              No Blog Articles Published Yet
            </h3>
            <p style="color: var(--text-secondary); font-size: var(--text-sm); margin-bottom: var(--space-4);">
              Create your first digital marketing article to start building organic SEO authority.
            </p>
            <button class="btn btn-primary btn-sm" onclick="openBlogModal()"><i class="fas fa-plus"></i> Write First Article</button>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Blog Modal -->
<div class="modal" id="blogModal">
  <div class="modal-backdrop" onclick="closeBlogModal()"></div>
  <div class="modal-content" style="max-width: 820px; max-height: 90vh; display: flex; flex-direction: column;">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-newspaper"></i> <span id="modalTitle">Write New Article</span></h3>
      <button class="modal-close" onclick="closeBlogModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <input type="hidden" name="action" id="formAction" value="create">
      <input type="hidden" name="id" id="blogId">
      <input type="hidden" name="existing_image" id="blogExistingImage">

      <!-- Tabs Strip -->
      <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border-light); padding: 12px 20px 8px; background: var(--bg-surface-secondary); overflow-x: auto; flex-shrink: 0;">
        <button type="button" class="btn btn-sm blog-tab-btn active" id="tabBtn-basic" onclick="switchBlogTab('basic')" style="border-radius: 20px;">
          <i class="fas fa-pen-nib"></i> Article & Details
        </button>
        <button type="button" class="btn btn-sm btn-ghost blog-tab-btn" id="tabBtn-content" onclick="switchBlogTab('content')" style="border-radius: 20px;">
          <i class="fas fa-align-left"></i> Body Content
        </button>
        <button type="button" class="btn btn-sm btn-ghost blog-tab-btn" id="tabBtn-media" onclick="switchBlogTab('media')" style="border-radius: 20px;">
          <i class="fas fa-image"></i> Featured Image
        </button>
        <button type="button" class="btn btn-sm btn-ghost blog-tab-btn" id="tabBtn-seo" onclick="switchBlogTab('seo')" style="border-radius: 20px;">
          <i class="fas fa-magnifying-glass-chart"></i> SEO Metadata
        </button>
      </div>

      <div style="overflow-y: auto; padding: 20px; flex: 1;">

        <!-- TAB 1: BASIC INFO -->
        <div id="tabContent-basic" class="blog-tab-pane">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-heading"></i> Article Title *</label>
            <input type="text" name="title" id="blogTitle" required class="form-input" placeholder="e.g. SEO vs Google Ads: Which One Delivers Faster ROI?" oninput="autoGenerateBlogSlug(this.value)">
          </div>

          <div class="form-row cols-2">
            <div class="form-group">
              <label class="form-label"><i class="fas fa-link"></i> URL Slug *</label>
              <input type="text" name="slug" id="blogSlug" required class="form-input" placeholder="e.g. seo-vs-google-ads-faster-roi">
              <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Public URL: <code>/blog/{slug}</code></div>
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fas fa-tags"></i> Category *</label>
              <input type="text" name="category" id="blogCategory" required class="form-input" placeholder="e.g. SEO Services, Marketing Strategy, PPC" list="categoryPresets">
              <datalist id="categoryPresets">
                <option value="Marketing Strategy">
                <option value="SEO Services">
                <option value="Startup Growth">
                <option value="Lead Generation">
                <option value="Google Ads">
                <option value="Social Media Marketing">
                <option value="Website Development">
              </datalist>
            </div>
          </div>

          <div class="form-row cols-3">
            <div class="form-group">
              <label class="form-label"><i class="fas fa-user"></i> Author</label>
              <input type="text" name="author" id="blogAuthor" class="form-input" placeholder="Cloud Aspire Team" value="Cloud Aspire Team">
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fas fa-calendar"></i> Published Date</label>
              <input type="date" name="published_date" id="blogDate" class="form-input" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fas fa-toggle-on"></i> Status</label>
              <select name="status" id="blogStatus" class="form-select">
                <option value="published">Published (Live)</option>
                <option value="draft">Draft (Hidden)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-align-left"></i> Excerpt / Summary *</label>
            <textarea name="excerpt" id="blogExcerpt" required class="form-textarea" style="min-height: 80px;" placeholder="Short 2-3 sentence teaser that appears on the card and in Google snippets..."></textarea>
          </div>
        </div>

        <!-- TAB 2: FULL CONTENT -->
        <div id="tabContent-content" class="blog-tab-pane" style="display: none;">
          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label class="form-label" style="margin: 0;"><i class="fas fa-file-alt"></i> Article Body (HTML Supported)</label>
              <span style="font-size: 11px; color: var(--text-secondary);">Supports &lt;p&gt;, &lt;h2&gt;, &lt;h3&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;blockquote&gt;</span>
            </div>
            <textarea name="content" id="blogContent" class="form-textarea" style="min-height: 280px; font-family: monospace; font-size: 13.5px;" placeholder="<p>Write your detailed article paragraphs here...</p>&#10;<h3>Key Subheading</h3>&#10;<p>Explanation and insights...</p>&#10;<ul>&#10;  <li>Action point 1</li>&#10;  <li>Action point 2</li>&#10;</ul>"></textarea>
          </div>
        </div>

        <!-- TAB 3: FEATURED IMAGE -->
        <div id="tabContent-media" class="blog-tab-pane" style="display: none;">
          <div class="form-group">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
              <label class="form-label" style="margin: 0;"><i class="fas fa-image"></i> Featured Blog Banner Image</label>
              <button type="button" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 3px 10px;" onclick="openMediaPickerForBlog()">
                <i class="fas fa-images" style="color: var(--primary);"></i> Browse Media Library
              </button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
              <!-- File Upload Dropzone -->
              <div style="border: 2px dashed var(--border-light); border-radius: var(--radius-md); padding: 18px; text-align: center; background: var(--bg-surface-secondary); cursor: pointer; transition: all 0.2s ease;" onclick="document.getElementById('blogImageFile').click()">
                <i class="fas fa-cloud-arrow-up" style="font-size: 28px; color: var(--primary); margin-bottom: 6px;"></i>
                <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">Click to Upload from Computer</div>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">JPG, PNG, GIF, WEBP (Max 5MB)</div>
                <input type="file" name="image_file" id="blogImageFile" accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;" onchange="previewBlogImage(this)">
              </div>

              <!-- Preview Box -->
              <div id="blogImgPreviewContainer" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 12px; min-height: 100px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-surface); text-align: center;">
                <img id="blogImgPreview" src="" alt="Preview" style="max-height: 90px; max-width: 100%; border-radius: var(--radius-sm); object-fit: cover; display: none; margin-bottom: 6px;">
                <div id="blogImgEmptyText" style="font-size: 12px; color: var(--text-tertiary);">
                  <i class="fas fa-image" style="font-size: 24px; display: block; margin-bottom: 4px;"></i>
                  No Image Selected
                </div>
                <button type="button" id="blogImgClearBtn" class="btn btn-ghost btn-sm" style="display: none; color: var(--danger); font-size: 11px; padding: 2px 8px; margin-top: 4px;" onclick="clearBlogImage()">
                  <i class="fas fa-trash-alt"></i> Remove
                </button>
              </div>
            </div>

            <!-- Direct URL Input -->
            <div style="margin-top: 12px;">
              <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;"><i class="fas fa-link"></i> Or Enter Image Path / Direct URL:</div>
              <input type="text" name="image" id="blogImage" placeholder="uploads/blogs/blog_... or https://..." class="form-input" style="font-size: 12px; padding: 8px 12px;" oninput="previewBlogUrl(this.value)">
            </div>
          </div>
        </div>

        <!-- TAB 4: SEO METADATA -->
        <div id="tabContent-seo" class="blog-tab-pane" style="display: none;">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-heading"></i> Custom SEO Meta Title</label>
            <input type="text" name="seo_title" id="blogSeoTitle" class="form-input" placeholder="e.g. SEO vs Google Ads: ROI-Driven Strategy Guide - Cloud Aspire">
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Recommended: 50-60 characters. Appears as page title in Google search results.</div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-paragraph"></i> Meta Description</label>
            <textarea name="meta_description" id="blogMetaDesc" class="form-textarea" style="min-height: 80px;" placeholder="Compare SEO and Google Ads to discover which delivers faster ROI for small businesses and marketing leads..."></textarea>
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Recommended: 140-160 characters. Search engines use this snippet.</div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-tags"></i> Meta Keywords</label>
            <input type="text" name="meta_keywords" id="blogMetaKeywords" class="form-input" placeholder="e.g. seo vs google ads, faster roi, ppc marketing pondicherry">
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Comma separated keywords.</div>
          </div>
        </div>

      </div>

      <div class="modal-footer" style="padding: 14px 20px; background: var(--bg-surface-secondary); border-top: 1px solid var(--border-light); margin-top: 0;">
        <button type="submit" class="btn btn-primary" style="flex: 1;">
          <i class="fas fa-save"></i> Save & Publish Article
        </button>
        <button type="button" class="btn btn-secondary" onclick="closeBlogModal()">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function switchBlogTab(tabName) {
  document.querySelectorAll('.blog-tab-btn').forEach(b => {
    b.classList.remove('active');
    b.classList.add('btn-ghost');
  });
  document.querySelectorAll('.blog-tab-pane').forEach(p => p.style.display = 'none');

  const btn = document.getElementById('tabBtn-' + tabName);
  if (btn) {
    btn.classList.add('active');
    btn.classList.remove('btn-ghost');
  }
  const pane = document.getElementById('tabContent-' + tabName);
  if (pane) pane.style.display = 'block';
}

function autoGenerateBlogSlug(text) {
  const slugInput = document.getElementById('blogSlug');
  const action = document.getElementById('formAction').value;
  if (action === 'create' && slugInput) {
    slugInput.value = text.toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
}

function previewBlogImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.getElementById('blogImgPreview');
      const emptyText = document.getElementById('blogImgEmptyText');
      const clearBtn = document.getElementById('blogImgClearBtn');
      img.src = e.target.result;
      img.style.display = 'block';
      emptyText.style.display = 'none';
      clearBtn.style.display = 'inline-flex';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewBlogUrl(url) {
  const img = document.getElementById('blogImgPreview');
  const emptyText = document.getElementById('blogImgEmptyText');
  const clearBtn = document.getElementById('blogImgClearBtn');
  if (url && url.trim()) {
    let finalSrc = url.trim();
    if (!finalSrc.startsWith('http://') && !finalSrc.startsWith('https://') && !finalSrc.startsWith('/')) {
      finalSrc = '../' + finalSrc;
    }
    img.src = finalSrc;
    img.style.display = 'block';
    emptyText.style.display = 'none';
    clearBtn.style.display = 'inline-flex';
  } else if (!document.getElementById('blogImageFile').value) {
    img.style.display = 'none';
    emptyText.style.display = 'block';
    clearBtn.style.display = 'none';
  }
}

function clearBlogImage() {
  document.getElementById('blogImageFile').value = '';
  document.getElementById('blogImage').value = '';
  document.getElementById('blogExistingImage').value = '';
  const img = document.getElementById('blogImgPreview');
  img.src = '';
  img.style.display = 'none';
  document.getElementById('blogImgEmptyText').style.display = 'block';
  document.getElementById('blogImgClearBtn').style.display = 'none';
}

function openBlogModal() {
  document.getElementById('modalTitle').textContent = 'Write New Article';
  document.getElementById('formAction').value = 'create';
  document.getElementById('blogId').value = '';
  document.getElementById('blogTitle').value = '';
  document.getElementById('blogSlug').value = '';
  document.getElementById('blogCategory').value = 'Digital Marketing';
  document.getElementById('blogAuthor').value = 'Cloud Aspire Team';
  document.getElementById('blogDate').value = '<?= date('Y-m-d') ?>';
  document.getElementById('blogStatus').value = 'published';
  document.getElementById('blogExcerpt').value = '';
  document.getElementById('blogContent').value = '';
  document.getElementById('blogSeoTitle').value = '';
  document.getElementById('blogMetaDesc').value = '';
  document.getElementById('blogMetaKeywords').value = '';
  clearBlogImage();
  switchBlogTab('basic');
  document.getElementById('blogModal').classList.add('active');
}

function editBlog(b) {
  document.getElementById('modalTitle').textContent = 'Edit Article';
  document.getElementById('formAction').value = 'update';
  document.getElementById('blogId').value = b.id;
  document.getElementById('blogTitle').value = b.title || '';
  document.getElementById('blogSlug').value = b.slug || '';
  document.getElementById('blogCategory').value = b.category || 'Digital Marketing';
  document.getElementById('blogAuthor').value = b.author || 'Cloud Aspire Team';
  document.getElementById('blogDate').value = b.published_date || '<?= date('Y-m-d') ?>';
  document.getElementById('blogStatus').value = b.status || 'published';
  document.getElementById('blogExcerpt').value = b.excerpt || '';
  document.getElementById('blogContent').value = b.content || '';
  document.getElementById('blogSeoTitle').value = b.seo_title || '';
  document.getElementById('blogMetaDesc').value = b.meta_description || '';
  document.getElementById('blogMetaKeywords').value = b.meta_keywords || '';

  document.getElementById('blogImageFile').value = '';
  document.getElementById('blogExistingImage').value = b.image || '';
  document.getElementById('blogImage').value = b.image || '';

  if (b.image) {
    previewBlogUrl(b.image);
  } else {
    clearBlogImage();
  }

  switchBlogTab('basic');
  document.getElementById('blogModal').classList.add('active');
}

function closeBlogModal() {
  document.getElementById('blogModal').classList.remove('active');
}

function openMediaPickerForBlog() {
  openUniversalMediaPicker({
    folder: 'blogs',
    targetInput: '#blogImage',
    targetPreview: '#blogImgPreview',
    onSelect: function(file) {
      document.getElementById('blogImageFile').value = '';
      document.getElementById('blogExistingImage').value = file.relative_path;
      document.getElementById('blogImgEmptyText').style.display = 'none';
      document.getElementById('blogImgClearBtn').style.display = 'inline-flex';
    }
  });
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/media-picker-modal.php'; ?>
<?php require_once __DIR__ . '/../../../admin/includes/footer-pro.php'; ?>
