<?php
/**
 * Footer Template
 * Common footer for all pages
 */
?>
    </main>

    <!-- Footer -->
    <footer class="mt-5 pt-5 pb-3">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-4 footer-section">
                    <h5>About ICT Repository</h5>
                    <p>A centralized platform for managing and sharing ICT research papers and academic works.</p>
                </div>
                <div class="col-md-4 footer-section">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="../index.php">Browse Research</a></li>
                        <li><a href="../student/login.php">Student Login</a></li>
                        <li><a href="../admin/login.php">Admin Portal</a></li>
                    </ul>
                </div>
                <div class="col-md-4 footer-section">
                    <h5>Contact</h5>
                    <p>
                        Email: <a href="mailto:info@ictresearch.edu">info@ictresearch.edu</a><br>
                        Phone: <a href="tel:+1234567890">+1 (234) 567-890</a>
                    </p>
                </div>
            </div>

            <div class="footer-divider">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; 2026 ICT Research Repository. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <p class="mb-0">
                            <a href="#">Privacy Policy</a> | 
                            <a href="#">Terms of Service</a> | 
                            <a href="#">Accessibility</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
