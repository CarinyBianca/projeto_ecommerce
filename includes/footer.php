    </main>
<footer class="text-white py-4 mt-auto" style="background-color: var(--primary-color);">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><i class="fas fa-store me-2"></i>Crow Tech</h5>
                <p class="mb-0">Sua loja de tecnologia preferida</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5><i class="fas fa-map-marker-alt me-2"></i>Endereço</h5>
                <p class="mb-0">Rua Exemplo, 123<br>Belém - PA</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5><i class="fas fa-envelope me-2"></i>Contato</h5>
                <p class="mb-0">
                    Email: contato@crowtech.com<br>
                    Tel: (11) 1234-5678
                </p>
            </div>
        </div>
        <hr class="border-light">
        <div class="row">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> Crow Tech - Todos os direitos reservados</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
<?php if (!empty($js_adicional)): ?>
    <script><?= $js_adicional ?></script>
<?php endif; ?>
</body>
</html>
