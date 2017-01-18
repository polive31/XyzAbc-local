<?php

add_filter( 'wpurp_output_recipe', 'wpurp_custom_template', 10, 2 );

function wpurp_custom_template( $content, $recipe )
{
	ob_start();
	?>

	<div class="recipe">
		
		<div class="recipe-top">
			
			<?php
			echo $recipe->description();;
			?>	
			
			<div class="recipe-buttons">
				<?php
					$print_button = new WPURP_Template_Recipe_Print_Button();
					echo $print_button->output( $recipe );
				?>
			</div>
			
		</div>
		
		<div class="recipe-container">
			
			<div class="image-container">
				<div class="clearfix">
				  <a href="<?php echo $recipe->featured_image_url('full');?>">
						<img src="<?php echo $recipe->featured_image_url('horizontal-thumbnail');?>">
					</a>
				</div>
				<div class="clearfix">
					[custom-gallery size="mini-thumbnail" link="file" columns="4" gallery-id="joined-pics"]
				</div>
			</div>
		
			<div class="info-container">
				<?php
					//$star_rating = new WPURP_Template_Recipe_Stars();
					//echo $star_rating->output( $recipe );
					
					$rating = output_recipe_rating( get_the_ID());
					echo '<div class="label-container"><div class="recipe-label rating" title="Votes &#013;Voters" id="stars-' . $rating['stars'] . '"></div></div>';
				?>
				
				<?php
					// Servings
					$test = $recipe->servings_normalized();
					if ($test!='') {
						$servings = '<div class="label-container"><div id="servings" class="recipe-label">' . __('Serves','foodiepro') . '</div><input type="number" min="1" class="adjust-recipe-servings" data-original="' . $recipe->servings_normalized() . '" data-start-servings="' . $recipe->servings_normalized() . '" value="' . $recipe->servings_normalized() . '"/> ' . $recipe->servings_type() . '</div>';
						echo $servings;
					}
					
					// Prep time
					$test = $recipe->prep_time();
					if ($test!='') {
						$html = '<div class="label-container"><div id="prep" class="recipe-label">' . __('Preparation','foodiepro') . '</div>' . $test . ' ' . $recipe->prep_time_text() . '</div>';
						echo $html;
					}
					
					// Prep time
					$test = $recipe->cook_time();
					if ($test!='') {
						$html= '<div class="label-container"><div id="cook" class="recipe-label">' . __('Cooking','foodiepro') . '</div>' . $test . ' ' . $recipe->cook_time_text() . '</div>';
						echo $html;
						}
					
					$test = $recipe->passive_time();
					if ($test!='') {
						$html = '<div class="label-container"><div id="wait" class="recipe-label">' . __('Wait','foodiepro') . '</div>' . $test . ' ' . $recipe->passive_time_text() . '</div>';
						echo $html;					
					}
				?>
				
				
			</div>		
			
		</div>
		
		<div class="recipe-container">
			
			<div class="ingredients-container">
				<?php
				// Method "with Template"
					//$ingredient_list = new WPURP_Template_Recipe_Ingredients();
					//echo $ingredient_list->output( $recipe );
					
					
				// Method "with custom function"
					echo custom_ingredients_list($recipe,'');
					
				?>
			</div>

			<?php
				$instructions_list = new WPURP_Template_Recipe_Instructions();
				echo $instructions_list->output( $recipe );
			?>
		
		</div>
		
		<div class="recipe-container">
			<?php
			$test = $recipe->notes();
			if ($test!='') {
				$html= '<h3>' . __('Notes','foodiepro') . '</h3>';
				$html.= '<div class="label-container">' . $test . '</div>';
				echo $html;
				}
			?>
		</div>
		
	</div>

	<?php
    $output = ob_get_contents();
    ob_end_clean();

	return $output;
}


function custom_ingredients_list( $recipe, $args ) {
    $out = '';
    $previous_group = '';
    $vocals = array('a','e','i','o','u');
    
    foreach( $recipe->ingredients() as $ingredient ) {

        if( WPUltimateRecipe::option( 'ignore_ingredient_ids', '' ) != '1' && isset( $ingredient['ingredient_id'] ) ) {
            $term = get_term( $ingredient['ingredient_id'], 'ingredient' );
            if ( $term !== null && !is_wp_error( $term ) ) {
                $ingredient['ingredient'] = $term->name;
            }
        }

        if( isset($ingredient['group'] ) && $ingredient['group'] != $previous_group ) {
            $out .= '<li class="ingredient-group">' . $ingredient['group'] . '</li>';
            $previous_group = $ingredient['group'];
        }

        $fraction = WPUltimateRecipe::option('recipe_adjustable_servings_fractions', '0') == '1' ? true : false;
        $fraction = strpos($ingredient['amount'], '/') === false ? $fraction : true;

        $meta = WPUltimateRecipe::option( 'recipe_metadata_type', 'json-inline' ) != 'json' && $args['template_type'] == 'recipe' && $args['desktop'] ? ' itemprop="recipeIngredient"' : '';

        $out .= '<li class="wpurp-recipe-ingredient"' . $meta . '>';
        $out .= '<span class="recipe-ingredient-quantity-unit"><span class="wpurp-recipe-ingredient-quantity recipe-ingredient-quantity" data-normalized="'.$ingredient['amount_normalized'].'" data-fraction="'.$fraction.'" data-original="'.$ingredient['amount'].'">'.$ingredient['amount'].'</span> <span class="wpurp-recipe-ingredient-unit recipe-ingredient-unit" data-original="'.$ingredient['unit'].'">'.$ingredient['unit'].'</span></span>';

        $taxonomy = get_term_by('name', $ingredient['ingredient'], 'ingredient');
        $taxonomy_slug = is_object( $taxonomy ) ? $taxonomy->slug : $args['ingredient_name'];

        $plural = WPURP_Taxonomy_MetaData::get( 'ingredient', $taxonomy_slug, 'plural' );
        $plural = is_array( $plural ) ? false : $plural;
        $plural_data = $plural ? ' data-singular="' . esc_attr( $ingredient['ingredient'] ) . '" data-plural="' . esc_attr( $plural ) . '"' : '';

        $out .= ' <span class="wpurp-recipe-ingredient-name recipe-ingredient-name"' . $plural_data . '>';

				$ingredient_name = strtolower( esc_attr( $ingredient['ingredient'] ) );
				$first = $ingredient_name[0];
				
				if ( $ingredient['unit']!='' ) {
					if ( in_array($first, $vocals) )
						$out .= _x("d'",'vowel','foodiepro');
					else 
						$out .= _x("de ",'consonant','foodiepro');					
				}

        $ingredient_links = WPUltimateRecipe::option('recipe_ingredient_links', 'archive_custom');

        $closing_tag = '';
        if ( !empty( $taxonomy ) && $ingredient_links != 'disabled' ) {

            if( $ingredient_links == 'archive_custom' || $ingredient_links == 'custom' ) {
                $custom_link = WPURP_Taxonomy_MetaData::get( 'ingredient', $taxonomy_slug, 'link' );
            } else {
                $custom_link = false;
            }

            if( WPURP_Taxonomy_MetaData::get( 'ingredient', $taxonomy_slug, 'hide_link' ) !== '1' ) {
                if( $custom_link !== false && $custom_link !== '' ) {
                    $nofollow = WPUltimateRecipe::option( 'recipe_ingredient_custom_links_nofollow', '0' ) == '1' ? ' rel="nofollow"' : '';

                    $out .= '<a href="'.$custom_link.'" class="custom-ingredient-link" target="'.WPUltimateRecipe::option( 'recipe_ingredient_custom_links_target', '_blank' ).'"' . $nofollow . '>';
                    $closing_tag = '</a>';
                } else if( $ingredient_links != 'custom' ) {
                    $out .= '<a href="'.get_term_link( $taxonomy_slug, 'ingredient' ).'">';
                    $closing_tag = '</a>';
                }
            }
        }

        $out .= $plural && $ingredient['amount_normalized'] != 1 ? $plural : $ingredient['ingredient'];
        $out .= $closing_tag;
        $out .= '</span>';

        if( $ingredient['notes'] != '' ) {
            $out .= ' ';
            $out .= '<span class="wpurp-recipe-ingredient-notes recipe-ingredient-notes">'.$ingredient['notes'].'</span>';
        }

        $out .= '</li>';
    }

    return $out;
		}
    
?>