(function (wp, $) {
  /**
   * Consults values to determine whether the editor is busy saving a post.
   * Includes checks on whether the save button is busy.
   *
   * @returns {boolean} Whether the editor is on a busy save state.
   */
  function isSavingPost() {
    // State data necessary to establish if a save is occuring.
    const isSaving =
      wp.data.select('core/editor').isSavingPost() ||
      wp.data.select('core/editor').isAutosavingPost();
    const isSaveable = wp.data.select('core/editor').isEditedPostSaveable();
    const isPostSavingLocked = wp.data
      .select('core/editor')
      .isPostSavingLocked();
    const hasNonPostEntityChanges = wp.data
      .select('core/editor')
      .hasNonPostEntityChanges();
    const isAutoSaving = wp.data.select('core/editor').isAutosavingPost();
    const isButtonDisabled = isSaving || !isSaveable || isPostSavingLocked;

    // Reduces state into checking whether the post is saving and that the save button is disabled.
    const isBusy = !isAutoSaving && isSaving;
    const isNotInteractable = isButtonDisabled && !hasNonPostEntityChanges;

    return isBusy && isNotInteractable;
  }

  var wasSaving = isSavingPost();

  //type can be success, info, warning, error
  function displayNotice(type, errorMessage) {
    wp.data.dispatch('core/notices').createNotice(
      type, // Can be one of: success, info, warning, error.
      errorMessage,
      {
        isDismissible: true, // Whether the user can dismiss the notice.
        __unstableHTML: true,
      }
    );
  }

  wp.data.subscribe(() => {
    // New saving state
    let isSaving = isSavingPost();

    // It is done saving if it was saving and it no longer is.
    let isDoneSaving = wasSaving && !isSaving;

    // Update value for next use.
    wasSaving = isSaving;

    if (isDoneSaving) {
      const data = {
        action: 'block_editor_notices',
        // eslint-disable-next-line no-undef
        security: ajax_var.nonce,
      };

      // eslint-disable-next-line no-undef
      $.post(ajaxurl, data, function (response) {
        if (response?.hasError === true) {
          let message_arr = response?.message || {
            error: ['WP Engine Smart Search error has occurred!'],
          };
          Object.keys(message_arr).forEach((type) => {
            message_arr[type].forEach((msg) => displayNotice(type, msg));
          });
        }
      });
    } // End of isDoneSaving
  });
  // eslint-disable-next-line no-undef
})(window.wp, jQuery);
