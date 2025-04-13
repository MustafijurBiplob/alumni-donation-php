</div> <!-- /container -->

        <footer class="bg-light text-center text-lg-start mt-5">
          <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © <?php echo date("Y"); ?> কে এম সি কে এস
          </div>
        </footer>

        <!-- Add Bootstrap JS or other frameworks if desired -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Add custom JS file if needed -->
        <!-- <script src="<?php echo BASE_URL; ?>js/script.js"></script> -->
    </body>
    </html>
    <?php
    // Close database connection if open
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    ?>
