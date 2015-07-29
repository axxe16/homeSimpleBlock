<?php /*
Plugin Name: Home Simple Block
Description: Genera un widget adatto alla homepage, con titolo, un'immagine, un testo privo di tag html e un link ad una pagina o post. Il widget è realizzato a scopo didattico ed utilizzato in corsi di formazione all'utilizzo di Wordpress
Version: 1.0
Author: Alessandro Grassi
Author URI: http://www.semanticstone.net
License: GPL2
*/

	// Creating the widget 
class  HomeSimpleBlock extends WP_Widget {
	function __construct() {
		parent::__construct(
			'HomeSimpleBlock', // Base ID of your widget
			'Home Simple Block', // Widget name will appear in UI
			array( 'description' => 'Visualizza un\'immagine, un breve testo e linka a un post o ad una pagina') // Widget description 
		);
		//richiama la funzione di caricamento js per media uploader
	 	add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
	 	
	 	//includo lo stile nel frontend e faccio in modo che sia stampato per ultimo usando la priorità
	 	add_action('wp_enqueue_scripts',  array($this, 'upload_frontend_script'),10000);
	 	
	}

	// richiamo gli script per il media uploader
	public function upload_scripts()
    {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('upload_media_widget', plugin_dir_url(__FILE__) . 'upload-media.js', array('jquery'));
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
    }
	// richiamo gli stili per il frontend
	public function upload_frontend_script() {
		wp_enqueue_style('styleWidget', plugin_dir_url(__FILE__) . 'hsbStyle.css');
	}
	
	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		$url = $instance['url'];
		$txt = $instance['txt'];
		$id = $instance['id'];
		$shapeImg = $instance['shapeImg'];
		$imgAttr = array (
			'class'	=> $shapeImg,
			'alt'   => trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ))
		);
		$hMin = $instance['hMin'];
		$hoverEffect = $instance['hoverEffect'];
		if ($hoverEffect == 'SI') {
			$hoverClass='hoverEffect';
		} else {
			$hoverClass='';
		}
		// before and after widget arguments sono definiti dal tema
		echo $args['before_widget'];		
		// Costruisco l'HTML del widget
		if (strlen($hMin)>0) {
			$hMin = 'style="min-height:' . $hMin . 'px"';
		} else {
			$hMin = '';
		}
		$htmlWidget  = '<div class="sb_widget" '. $hMin .'>';
		$htmlWidget .= '<figure class="sb_image text-center ' . $hoverClass . '">';
		$htmlWidget .= '<a href=' . $url . '>' . wp_get_attachment_image($id,array(360,270),0,$imgAttr) . '</a>';
		$htmlWidget .= '</figure>';
		$htmlWidget .= '<div class="sb_section">';
		if (!empty( $title )) {
			$htmlWidget .= '<h3 class="sb_title text-center"><a href=' . $url . '>' . $title . '</a></h3>';
		}
		if (strlen($txt)>0) {
			$htmlWidget .= '<div class="sb_text text-center">' . $txt . '</div>';
		}
		$htmlWidget .= '</div>';
		
		echo $htmlWidget;
		
		echo $args['after_widget'];
	}		
		
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = 'Nuovo titolo';
		}
		if ( isset( $instance[ 'url' ] ) ) {
			$url = $instance[ 'url' ];
		} else {
			$url = "";
		}
		if ( isset( $instance[ 'txt' ] ) ) {
			$txt = $instance[ 'txt' ];
		} else {
			$txt = "";
		}			
		//verifica se esiste una chiave "immagine"
		if(isset($instance['id']))
        {
            $id = $instance['id'];
        } else {
	    	$id = "";
	    }
	    //verifica se esiste una chiave "shape"
		if(isset($instance['shape']))
        {
            $shape = $instance['shape'];
        } else {
	    	$shape = "";
	    }
		//verifica se esiste una chiave "hMin"
		if(isset($instance['hMin']))
        {
            $hMin = $instance['hMin'];
        } else {
	    	$hMin = "";
	    }
	    //verifica se esiste una chiave "hoverEffect"
		if(isset($instance['hoverEffect']))
        {
            $hoverEffect = $instance['hoverEffect'];
        } else {
	    	$hoverEffect = "";
	    }
	    
		// ****************************************************************************************************************************
		// ******************************* Widget admin costruzione del form in area di amministrazione *******************************
		// ****************************************************************************************************************************
		?>
		
		<!-- ******************************* Campo Titolo: inserisco il titolo del widget *******************************-->
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Titolo</label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<!-- ******************************* Fine Campo Titolo *******************************-->
		
		
		<!-- ******************************* Campo Testo: inserisco il testo *******************************-->
		<p>
		<label for="<?php echo $this->get_field_id( 'txt' ); ?>">Testo [max 50 caratteri]</label> 
		<textarea  class="widefat" id="<?php echo $this->get_field_id( 'txt' ); ?>" name="<?php echo $this->get_field_name( 'txt' ); ?>" ><?php echo esc_attr( $txt ); ?></textarea>
		</p>
		<!-- ******************************* Fine Campo Testo *******************************-->
		
		
		<!-- ******************************* Campo Select: 2 wp-query elencano pagine e post *******************************-->
		<p>
		<label for="<?php echo $this->get_field_id( 'url' ); ?>">URL</label> 
		<select id="selectContent">
			<option value="http://">Custom</option>
			<?php 
			$pagine = new WP_Query( array(
				'post_type' => 'page',
				'posts_per_page' => -1
			));
			
			echo '<optgroup label="pagine">';
			while( $pagine->have_posts() ):
				$pagine->the_post();
				//print_r($pagine);
				$selected="";
				if (get_the_permalink($pagine->post->ID)==esc_attr( $url )) $selected = 'selected="selected"';
				if (strlen($pagine->post->post_title)==0) {
					$itemTitle = "**pagina senza titolo**";
				} else {
					$itemTitle = $pagine->post->post_title;
				}
				echo '<option title="' . $pagine->post->post_title . '" value="'. get_the_permalink($pagine->post->ID) .'" '. $selected .'>' . $itemTitle  . '</option>';
			endwhile;
			echo '</optgroup>';
			// Ripristina Query & Post Data originali
			wp_reset_query();
			wp_reset_postdata();
			
			$posts = new WP_Query( array(
				'post_type' => 'post',
				'posts_per_page' => -1
			));
			echo '<optgroup label="posts">';	
			while( $posts->have_posts() ):
				$posts->the_post();
				//print_r($pagine);
				$selected="";
				if (get_the_permalink($posts->post->ID)==esc_attr( $url )) $selected = 'selected="selected"';
				if (strlen($posts->post->post_title)==0) {
					$itemTitle = "**post senza titolo**";
				} else {
					$itemTitle = substr($posts->post->post_title,0,25);
					if (strlen($posts->post->post_title) > 25)
						$itemTitle .= '...'; 
				}
				echo '<option title="' . $posts->post->post_title . '" value="'. get_the_permalink($posts->post->ID) .'" '. $selected .'>' . $itemTitle  . '</option>';
			endwhile;
			echo '</optgroup>';
			// Ripristina Query & Post Data originali
			wp_reset_query();
			wp_reset_postdata();
			?>
		</select>
		<input id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>" />
		</p>
		<!-- ******************************* Fine Select *******************************-->
		
		
		<!-- ******************************* Campo ID immagine: chiamo il media manager e stampo l'id dell'immagine *******************************-->
		<p>
            <label for="<?php echo $this->get_field_name( 'id' ); ?>">ID</label>
            <input name="<?php echo $this->get_field_name( 'id' ); ?>" id="<?php echo $this->get_field_id( 'id' ); ?>" type="number" size="36"  value="<?php echo $id; ?>" />
            <input class="upload_image_button" type="button" value="Carica Immagine [ID]" />
        </p>
        <!-- ******************************* Fine campo ID *******************************-->
        
        
        <!-- ******************************* Campo Radio cornice immagine: seleziono la classe da attribuire all'immagine *******************************-->
        
        <p>
	        <label for="<?php echo $this->get_field_name( 'shapeImg' ); ?>">Cornice immagine</label><br>
	        <input type="radio" name="<?php echo $this->get_field_name( 'shapeImg'); ?>" value="" <?php if($instance['shapeImg'] == '') echo 'checked="checked"'; ?>>none<br>
	        <input type="radio" name="<?php echo $this->get_field_name( 'shapeImg'); ?>" value="img-rounded" <?php if($instance['shapeImg'] =='img-rounded') echo 'checked="checked"'; ?>>rounded<br>
			<input type="radio" name="<?php echo $this->get_field_name( 'shapeImg'); ?>" value="img-thumbnail" <?php if($instance['shapeImg'] == 'img-thumbnail') echo 'checked="checked"'; ?>>thumbnail<br>
        </p>
        <!-- ******************************* Fine Campo Radio *******************************-->
		
		 <!-- ******************************* Campo Radio effetto hover su immagine: applico o meno la classe per effetto hover *******************************-->
        <p>
	        <label for="<?php echo $this->get_field_name( 'hoverEffect' ); ?>">Effetto hover su immagine</label><br>
	        <input type="radio" name="<?php echo $this->get_field_name( 'hoverEffect'); ?>" value="NO" <?php if($instance['hoverEffect'] == 'NO' || $instance['hoverEffect'] =='') echo 'checked="checked"'; ?>>NO
	        <input type="radio" name="<?php echo $this->get_field_name( 'hoverEffect'); ?>" value="SI" <?php if($instance['hoverEffect'] =='SI') echo 'checked="checked"'; ?>>SI<br>
        </p>
        <!-- ******************************* Fine Campo Radio *******************************-->
		
		
		<!-- ******************************* Campo Altezza Minima: imposta l'altezza minima del box *******************************-->
		<p>
		<label for="<?php echo $this->get_field_id( 'hMin' ); ?>">Altezza Minima (es. 200)</label> 
		<input id="<?php echo $this->get_field_id( 'hMin' ); ?>" name="<?php echo $this->get_field_name( 'hMin' ); ?>" type="text" value="<?php echo esc_attr( $hMin ); ?>" />
		</p>
		<!-- ******************************* Fine Campo Altezza Minima *******************************-->
		
		
		<?php 
		// ****************************************************************************************************************************
		// ******************************* Fine area di amministrazione ***************************************************************
		// ****************************************************************************************************************************	
	}
	// fine public function form
	
	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['txt'] = ( ! empty( $new_instance['txt'] ) ) ? strip_tags( $new_instance['txt'] ) : '';
		$instance['url'] = ( ! empty( $new_instance['url'] ) ) ? strip_tags( $new_instance['url'] ) : '';
		$instance['id'] = ( ! empty( $new_instance['id'] ) ) ? strip_tags( $new_instance['id'] ) : '';
		$instance['shapeImg'] = ( ! empty( $new_instance['shapeImg'] ) ) ? strip_tags( $new_instance['shapeImg'] ) : '';
		$instance['hMin'] = ( ! empty( $new_instance['hMin'] ) ) ? strip_tags( $new_instance['hMin'] ) : '';
		$instance['hoverEffect'] = ( ! empty( $new_instance['hoverEffect'] ) ) ? strip_tags( $new_instance['hoverEffect'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here

// Register and load the widget
function HomeSimpleBlock_load() {
	register_widget( 'HomeSimpleBlock' );
}
add_action( 'widgets_init', 'HomeSimpleBlock_load' );
?>