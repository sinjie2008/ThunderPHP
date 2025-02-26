<?php if(user_can('view_plugins')):?>

<div class="container-fluid p-4">
    <?php if(!empty($pager)):?>
    <label class="mb-3"><i>Page: <?=$pager->page_number?></i></label>
    <?php endif?>

    <div class="row mb-4">
        <div class="col-md-8">
            <h4>Plugins</h4>
            <p class="text-muted">Manage your installed plugins</p>
            <div class="search-box mt-3">
                <input type="text" id="plugin-search" class="form-control" placeholder="Search plugins by name, description, or author...">
            </div>
        </div>
        <div class="col-md-4 text-end">
            <?php if(user_can('upload_plugin')):?>
            <form method="post" enctype="multipart/form-data" class="d-inline">
                <?=csrf()?>
                <input type="file" name="plugin_zip" accept=".zip" class="me-2" id="plugin_zip">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-upload"></i> Upload Plugin
                </button>
            </form>
            <?php endif?>
        </div>
    </div>

    <?php if(!empty($plugins)):?>
        <div class="row g-4" id="plugin-list">
        <?php foreach($plugins as $plugin):?>
            <div class="col-12 col-md-6 col-lg-4 plugin-item" 
                 data-name="<?=esc(strtolower($plugin->name ?? ''))?>"
                 data-description="<?=esc(strtolower($plugin->description ?? ''))?>"
                 data-author="<?=esc(strtolower($plugin->author ?? ''))?>">
                <div class="card h-100 plugin-card">
                    <div class="card-header bg-light d-flex align-items-center p-3" style="height: 100px;">
                        <div class="plugin-icon me-3">
                            <?php if(!empty($plugin->image)):?>
                                <img src="<?=get_image($plugin->image)?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;" alt="<?=esc($plugin->name ?? 'Plugin')?>">
                            <?php else:?>
                                <div class="rounded bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fa-solid fa-puzzle-piece fa-2x text-muted"></i>
                                </div>
                            <?php endif?>
                        </div>
                        <div>
                            <h5 class="card-title mb-1"><?=esc($plugin->name ?? 'Unnamed Plugin')?></h5>
                            <div class="text-muted small">By <?=esc($plugin->author ?? 'Unknown')?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted"><?=esc($plugin->description ?? 'No description available')?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark">Version <?=esc($plugin->version ?? 'N/A')?></span>
                            <?php if(user_can('uninstall_plugin')):?>
                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to uninstall this plugin? This action cannot be undone.')">
                                <?=csrf()?>
                                <input type="hidden" name="action" value="uninstall">
                                <input type="hidden" name="plugin_id" value="<?=esc($plugin->id)?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i> Uninstall
                                </button>
                            </form>
                            <?php endif?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach?>
        </div>
    <?php else:?>
        <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i>No plugins found. Upload a plugin to get started.
        </div>
    <?php endif?>

    <?php if(!empty($pager)):?>
    <div class="d-flex justify-content-center mt-4">
        <?php $pager->display()?>    
    </div>
    <?php endif?>
</div>

<?php else:?>
    <div class="alert alert-danger text-center">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>Access denied. You don't have permission for this action
    </div>
<?php endif?>

<style>
.plugin-card {
    transition: all 0.2s ease;
    border: 1px solid rgba(0,0,0,0.1);
}
.plugin-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    transform: translateY(-2px);
}
.search-box {
    max-width: 500px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('plugin-search');
    const pluginItems = document.querySelectorAll('.plugin-item');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();

        pluginItems.forEach(item => {
            const name = item.dataset.name;
            const description = item.dataset.description;
            const author = item.dataset.author;

            if (name.includes(searchTerm) || 
                description.includes(searchTerm) || 
                author.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>