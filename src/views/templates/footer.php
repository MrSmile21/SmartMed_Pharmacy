<?php
// src/views/templates/footer.php
?>
    </main>
    <footer class="main-footer">
        <div class="container footer-container">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-pills"></i> SmartMed
                </div>
                <p>&copy; <?php echo date("Y"); ?> SmartMed Pvt Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="js/script.js"></script>
    </body>
</html>
<?php
global $mysqli;
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>