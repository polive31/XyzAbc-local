//jQuery(document).ready(function() {
//	console.log('Custom favorite recipe loaded');
//});
//

jQuery(document).ready(function(){
    jQuery(document).on('click', '.wpurp-recipe-favorite.logged-in', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var button = jQuery(this);
        
				console.log('ADD TO FAVORITES CLICK !!!!');

        var recipeId = button.data('recipe-id');
				//console.log('Recipe ID :'+recipeId);

        var data = {
            action: 'favorite_recipe',
            security: wpurp_favorite_recipe.nonce,
            recipe_id: recipeId
        };

        jQuery.post(wpurp_favorite_recipe.ajaxurl, data, function(html) {
	        if(!button.hasClass('is-favorite')) {
	            // Activate shopping list button
	          button.addClass('is-favorite');
	          }
	        else {
						button.removeClass('is-favorite');
	        }
        });
    });
});