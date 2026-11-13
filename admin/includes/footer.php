        </main>
    </div>

    <!-- Bootstrap 5.3.3 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto dismiss flash alerts
        document.querySelectorAll('.alert').forEach(el => {
            setTimeout(() => {
                const bAlert = bootstrap.Alert.getOrCreateInstance(el);
                if (bAlert) bAlert.close();
            }, 6000);
        });
    </script>
</body>
</html>

