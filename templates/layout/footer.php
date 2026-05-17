    </main>
    <footer>
        <p>&copy; 2026 DLE Games Daily</p>
    </footer>

    <?php if(isset($js) && is_array($js)): ?>
        <?php foreach($js as $script): ?>
            <script src="js/<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>