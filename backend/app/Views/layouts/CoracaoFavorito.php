<button type="button" class="bc-fav-coracao" data-fav-id="<?= (int)$anuncioId ?>" data-fav-url="<?= esc(site_url('anuncios/'.(int)$anuncioId.'/favoritar'),'attr') ?>" data-fav-estado="<?= esc(site_url('favoritos/estado'),'attr') ?>" aria-label="Adicionar aos favoritos" aria-pressed="false" disabled>
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
</button>
