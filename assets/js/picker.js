/**
 * Nearby image picker - vanilla JS, no dependencies.
 *
 * Markup contract (see includes/functions-pickers.php):
 *   .azrcrv-n-picker[data-target="{id}"]
 *     input#{id}[type=hidden]              <- value updated on selection
 *     input.azrcrv-n-picker-filter         <- optional live filter
 *     .azrcrv-n-picker-grid
 *       .azrcrv-n-picker-item[data-value][data-name]  <- one per option
 */
document.addEventListener( 'DOMContentLoaded', function () {

	document.querySelectorAll( '.azrcrv-n-picker' ).forEach( function ( picker ) {

		var hiddenInput = document.getElementById( picker.getAttribute( 'data-target' ) );
		var filterInput = picker.querySelector( '.azrcrv-n-picker-filter' );
		var items = picker.querySelectorAll( '.azrcrv-n-picker-item' );

		if ( ! hiddenInput ) {
			return;
		}

		function selectItem( item ) {
			items.forEach( function ( otherItem ) {
				otherItem.classList.remove( 'is-selected' );
				otherItem.setAttribute( 'aria-checked', 'false' );
			} );
			item.classList.add( 'is-selected' );
			item.setAttribute( 'aria-checked', 'true' );
			hiddenInput.value = item.getAttribute( 'data-value' );
			hiddenInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );

			// Move the newly-selected item to the front of the grid, so the
			// current selection stays visible at a glance without having to
			// scroll or filter for it.
			var grid = item.parentNode;
			if ( grid && grid.firstChild !== item ) {
				grid.insertBefore( item, grid.firstChild );
			}
		}

		items.forEach( function ( item ) {
			item.addEventListener( 'click', function () {
				selectItem( item );
			} );
			item.addEventListener( 'keydown', function ( event ) {
				if ( 'Enter' === event.key || ' ' === event.key ) {
					event.preventDefault();
					selectItem( item );
				}
			} );
		} );

		if ( filterInput ) {
			filterInput.addEventListener( 'input', function () {
				var term = filterInput.value.toLowerCase();
				items.forEach( function ( item ) {
					var name = ( item.getAttribute( 'data-name' ) || '' ).toLowerCase();
					item.style.display = ( '' === term || name.indexOf( term ) !== -1 ) ? '' : 'none';
				} );
			} );
		}
	} );
} );
