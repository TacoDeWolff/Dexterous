			</div>
		</section>

		<?php foreach ($_['footer_external_scripts'] as $external_script): ?><script src="<?php echo $external_script; ?>"></script><?php endforeach; ?>
		<?php if (isset($_['footer_script'])): ?><script src="/<?php echo $_['base_url'] . $_['footer_script']; ?>"></script><?php endif; ?>
		<!--<![endif]-->
	</body>
</html>