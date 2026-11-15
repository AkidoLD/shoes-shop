<?php

declare(strict_types=1);

?>
</main>

<footer class="footer" id="contact">

    <div class="container footer-inner">

        <div>

            <a href="index.php" class="logo">
                SHOES <span>- SHOP</span>
            </a>

            <p style="margin-top:15px; max-width:320px;">

                Découvre des chaussures adaptées
                à ton style et contacte-nous pour
                passer ta commande.

            </p>

        </div>

        <div>

            <h3>Navigation</h3>

            <div class="footer-links">

                <a href="index.php#accueil">Accueil</a>

                <a href="index.php#catalogue">Catalogue</a>

                <a href="index.php#nouveautes">Nouveautés</a>

            </div>

        </div>

        <div>

            <h3>Contact</h3>

            <div class="footer-links">

                <a href="<?php echo e($whatsapp_contact_url ?? '#'); ?>"
                   target="_blank"
                   rel="noopener">WhatsApp</a>

                <a href="index.php#catalogue">Nos chaussures</a>

            </div>

        </div>

    </div>

    <div class="footer-bottom">

        &copy; <?php echo date('Y'); ?> <?php echo e($config['site_name']); ?>.
        Tous droits réservés.

    </div>

</footer>

<script src="assets/js/app.js"></script>

</body>

</html>
