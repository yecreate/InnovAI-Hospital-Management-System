<?php
require_once 'header.php';

$doc = $_GET['doc'] ?? 'erd';

function get_sql_content($filename) {
    $path = '../' . $filename;
    if (file_exists($path)) {
        return htmlspecialchars(file_get_contents($path));
    }
    return "SQL file not found.";
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-warning pb-2 border-bottom border-warning"><i class="fas fa-file-alt me-2"></i> Project Blueprints & Documentation</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="list-group mb-4 shadow-sm border-warning">
            <a href="?doc=erd" class="list-group-item list-group-item-action bg-dark text-light border-secondary <?php echo $doc == 'erd' ? 'active bg-warning text-dark border-warning' : ''; ?>">
                <i class="fas fa-project-diagram me-2"></i> Project ERD (PDF)
            </a>
            <a href="?doc=schema" class="list-group-item list-group-item-action bg-dark text-light border-secondary <?php echo $doc == 'schema' ? 'active bg-warning text-dark border-warning' : ''; ?>">
                <i class="fas fa-table me-2"></i> Project Schema (PDF)
            </a>
            <a href="?doc=sql" class="list-group-item list-group-item-action bg-dark text-light border-secondary <?php echo $doc == 'sql' ? 'active bg-warning text-dark border-warning' : ''; ?>">
                <i class="fas fa-database me-2"></i> Hospital.sql (Code)
            </a>
        </div>
        
        <div class="card bg-dark border-info">
            <div class="card-body small text-info">
                <i class="fas fa-info-circle me-1"></i> These documents represent the "Constitutional Protocol" of the InnovAI Hospital Management System.
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-eye me-2"></i> Document Preview</span>
                <?php if($doc == 'erd' || $doc == 'schema'): ?>
                    <a href="../Project_<?php echo strtoupper($doc); ?>.pdf" target="_blank" class="btn btn-sm btn-gold"><i class="fas fa-external-link-alt"></i> Open Full PDF</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0" style="min-height: 80vh; background-color: #f8f9fa;">
                <?php if($doc == 'erd'): ?>
                    <embed src="../Project_ERD.pdf" type="application/pdf" width="100%" height="800px" />
                <?php elseif($doc == 'schema'): ?>
                    <embed src="../Project_Schema.pdf" type="application/pdf" width="100%" height="800px" />
                <?php elseif($doc == 'sql'): ?>
                    <pre class="m-0 p-3 bg-dark text-success" style="height: 800px; overflow: auto; font-family: 'Courier New', Courier, monospace; font-size: 0.85rem;"><code><?php echo get_sql_content('phpMyAdmin_(Hospital.sql).sql'); ?></code></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
