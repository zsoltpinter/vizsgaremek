    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="bi bi-car-front-fill"></i> AlapSzerviz.hu</h5>
                    <p>Magyarország legnagyobb autószerviz kereső platformja. Találd meg a számodra legmegfelelőbb szervizt!</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Gyors linkek</h5>
                    <ul class="list-unstyled">
                        <li><a href="/index.php">Főoldal</a></li>
                        <li><a href="/services.php">Szervizek keresése</a></li>
                        <?php if (is_logged_in()): ?>
                            <li><a href="/service_add.php">Szerviz hozzáadása</a></li>
                            <li><a href="/support.php">Support</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Kapcsolat</h5>
                    <p>
                        <i class="bi bi-envelope"></i> support@alapszerviz.hu<br>
                        <i class="bi bi-telephone"></i> +36 1 234 5678<br>
                        <i class="bi bi-geo-alt"></i> Budapest, Magyarország
                    </p>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> AlapSzerviz.hu - Minden jog fenntartva</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dark Mode Toggle -->
    <script>
        // Dark mode kezelés
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Ikon váltás
            const icon = document.getElementById('theme-icon');
            if (newTheme === 'dark') {
                icon.className = 'bi bi-sun-fill';
            } else {
                icon.className = 'bi bi-moon-stars-fill';
            }
        }
        
        // Oldal betöltéskor ellenőrizzük a mentett témát
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            const icon = document.getElementById('theme-icon');
            if (icon) {
                if (savedTheme === 'dark') {
                    icon.className = 'bi bi-sun-fill';
                } else {
                    icon.className = 'bi bi-moon-stars-fill';
                }
            }
        });
    </script>
</body>
</html>
