        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> Quản lý Sinh viên. Tất cả quyền được bảo lưu.<br>
                <small>Developed by <a href="https://github.com/lehuygiang28" target="_blank" rel="noopener noreferrer" class="text-decoration-none">github.com/lehuygiang28</a></small>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS 5.3.8 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    
    <?php if (isset($additionalScripts)): ?>
        <?php echo $additionalScripts; ?>
    <?php endif; ?>
</body>
</html>
<?php
// Flush output buffer nếu có
if (ob_get_level()) {
    ob_end_flush();
}
?>
