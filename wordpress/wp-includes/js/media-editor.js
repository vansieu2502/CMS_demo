/**
 * @output wp-includes/js/media-editor.js
 */

/* global getUserSetting, tinymce, QTags */

// WordPress, TinyMCE, and Media
// -----------------------------
(function($, _){
	/**
	 * Stores the editors' `wp.media.controller.Frame` instances.
	 *
	 * @static
	 */
	var workflows = {};

	/**
	 * A helper mixin function to avoid truthy and falsey values being
	 *   passed as an input that expects booleans. If key is undefined in the map,
	 *   but has a default value, set it.
	 *
	 * @param {Object} attrs Map of props from a shortcode or settings.
	 * @param {string} key The key within the passed map to check for a value.
	 * @return {mixed|undefined} The original or coerced value of key within attrs.
	 */
	wp.media.coerce = function ( attrs, key ) {
		if ( _.isUndefined( attrs[ key ] ) && ! _.isUndefined( this.defaults[ key ] ) ) {
			attrs[ key ] = this.defaults[ key ];
		} else if ( 'true' === attrs[ key ] ) {
			attrs[ key ] = true;
		} else if ( 'false' === attrs[ key ] ) {
			attrs[ key ] = false;
		}
		return attrs[ key ];
	};

	/** @namespace wp.media.string */
	wp.media.string = {
		/**
		 * Joins the `props` and `attachment` objects,
		 * outputting the proper object format based on the
		 * attachment's type.
		 *
		 * @param {Object} [props={}] Attachment details (align, link, size, etc).
		 * @param {Object} attachment The attachment object, media version of Post.
		 * @return {Object} Joined props
		 */
		props: function( props, attachment ) {
			var link, linkUrl, size, sizes,
				defaultProps = wp.media.view.settings.defaultProps;

			props = props ? _.clone( props ) : {};

			if ( attachment && attachment.type ) {
				props.type = attachment.type;
			}

			if ( 'image' === props.type ) {
				props = _.defaults( props || {}, {
					align:   defaultProps.align || getUserSetting( 'align', 'none' ),
					size:    defaultProps.size  || getUserSetting( 'imgsize', 'medium' ),
					url:     '',
					classes: []
				});
			}

			// All attachment-specific settings follow.
			if ( ! attachment ) {
				return props;
			}

			props.title = props.title || attachment.title;

			link = props.link || defaultProps.link || getUserSetting( 'urlbutton', 'file' );
			if ( 'file' === link || 'embed' === link ) {
				linkUrl = attachment.url;
			} else if ( 'post' === link ) {
				linkUrl = attachment.link;
			} else if ( 'custom' === link ) {
				linkUrl = props.linkUrl;
			}
			props.linkUrl = linkUrl || '';

			// Format properties for images.
			if ( 'image' === attachment.type ) {
				props.classes.push( 'wp-image-' + attachment.id );

				sizes = attachment.sizes;
				size = sizes && sizes[ props.size ] ? sizes[ props.size ] : attachment;

				_.extend( props, _.pick( attachment, 'align', 'caption', 'alt' ), {
					width:     size.width,
					height:    size.height,
					src:       size.url,
					captionId: 'attachment_' + attachment.id
				});
			} else if ( 'video' === attachment.type || 'audio' === attachment.type ) {
				_.extend( props, _.pick( attachment, 'title', 'type', 'icon', 'mime' ) );
			// Format properties for non-images.
			} else {
				props.title = props.title || attachment.filename;
				props.rel = props.rel || 'attachment wp-att-' + attachment.id;
			}

			return props;
		},
		/**
		 * Create link markup that is suitable for passing to the editor
		 *
		 * @param {Object} props Attachment details (align, link, size, etc).
		 * @param {Object} attachment The attachment object, media version of Post.
		 * @return {string} The link markup
		 */
		link: function( props, attachment ) {
			var options;

			props = wp.media.string.props( props, attachment );

			options = {
				tag:     'a',
				content: props.title,
				attrs:   {
					href: props.linkUrl
				}
			};

			if ( props.rel ) {
				options.attrs.rel = props.rel;
			}

			return wp.html.string( options );
		},
		/**
		 * Create an Audio shortcode string that is suitable for passing to the editor
		 *
		 * @param {Object} props Attachment details (align, link, size, etc).
		 * @param {Object} attachment The attachment object, media version of Post.
		 * @return {string} The audio shortcode
		 */
		audio: function( props, attachment ) {
			return wp.media.string._audioVideo( 'audio', props, attachment );
		},
		/**
		 * Create a Video shortcode string that is suitable for passing to the editor
		 *
		 * @param {Object} props Attachment details (align, link, size, etc).
		 * @param {Object} attachment The attachment object, media version of Post.
		 * @return {string} The video shortcode
		 */
		video: function( props, attachment ) {
			return wp.media.string._audioVideo( 'video', props, attachment );
		},
		/**
		 * Helper function to create a media shortcode string
		 *
		 * @access private
		 *
		 * @param {string} type The shortcode tag name: 'audio' or 'video'.
		 * @param {Object} props Attachment details (align, link, size, etc).
		 * @param {Object} attachment The attachment object, media version of Post.
		 * @return {string} The media shortcode
		 */
		_audioVideo: function( type, props, attachment ) {
			var shortcode, html, extension;

			props = wp.media.string.props( props, attachment );
			if ( props.link !== 'embed' ) {
				return wp.media.string.link( props );
			}

			shortcode = {};

			if ( 'video' === type ) {
				if ( attachment.image && -1 === attachment.image.src.indexOf( attachment.icon ) ) {
					shortcode.poster = attachment.image.src;
				}

				if ( attachment.width ) {
					shortcode.width = attachment.width;
				}

				if ( attachment.height ) {
					shortcode.height = attachment.height;
				}
			}

			extension = attachment.filename.split('.').pop();

			if ( _.contains( wp.media.view.settings.embedExts, extension ) ) {
				shortcode[extension] = attachment.url;
			} else {
				// Render unsupported audio and video files as links.
				return wp.media.string.link( props );
			}

			html = wp.shortcode.string({
				tag:     type,
				attrs:   shortcode
			});

			return html;
		},
		/**
		 * Create image markup, optionally with a link and/or wrapped in a caption shortcode,
		 *  that is suitable for passing to the editor
		 *
		 * @param {Object} props Attachment details (align, link, size, etc).
		 * @param {Object} attachment The attachment object, media version of Post.
		 * @return {string}
		 */
		image: function( props, attachment ) {
			var img = {},
				options, classes, shortcode, html;

			props.type = 'image';
			props = wp.media.string.props( props, attachment );
			classes = props.classes || [];

			img.src = ! _.isUndefined( attachment ) ? attachment.url : props.url;
			_.extend( img, _.pick( props, 'width', 'height', 'alt' ) );

			// Only assign the align class to the image if we're not printing
			// a caption, since the alignment is sent to the shortcode.
			if ( props.align && ! props.caption ) {
				classes.push( 'align' + props.align );
			}

			if ( props.size ) {
				classes.push( 'size-' + props.size );
			}

			img['class'] = _.compact( classes ).join(' ');

			// Generate `img` tag options.
			options = {
				tag:    'img',
				attrs:  img,
				single: true
			};

			// Generate the `a` element options, if they exist.
			if ( props.linkUrl ) {
				options = {
					tag:   'a',
					attrs: {
						href: props.linkUrl
					},
					content: options
				};
			}

			html = wp.html.string( options );

			// Generate the caption shortcode.
			if ( props.caption ) {
				shortcode = {};

				if ( img.width ) {
					shortcode.width = img.width;
				}

				if ( props.captionId ) {
					shortcode.id = props.captionId;
				}

				if ( props.align ) {
					shortcode.align = 'align' + props.align;
				}

				html = wp.shortcode.string({
					tag:     'caption',
					attrs:   shortcode,
					content: html + ' ' + props.caption
				});
			}

			return html;
		}
	};

	wp.media.embed = {
		coerce : wp.media.coerce,

		defaults : {
			url : '',
			width: '',
			height: ''
		},

		edit : function( data, isURL ) {
			var frame, props = {}, shortcode;

			if ( isURL ) {
				props.url = data.replace(/<[^>]+>/g, '');
			} else {
				shortcode = wp.shortcode.next( 'embed', data ).shortcode;

				props = _.defaults( shortcode.attrs.named, this.defaults );
				if ( shortcode.content ) {
					props.url = shortcode.content;
				}
			}

			frame = wp.media({
				frame: 'post',
				state: 'embed',
				metadata: props
			});

			return frame;
		},

		shortcode : function( model ) {
			var self = this, content;

			_.each( this.defaults, function( value, key ) {
				model[ key ] = self.coerce( model, key );

				if ( value === model[ key ] ) {
					delete model[ key ];
				}
			});

			content = model.url;
			delete model.url;

			return new wp.shortcode({
				tag: 'embed',
				attrs: model,
				content: content
			});
		}
	};

	/**
	 * @class wp.media.collection
	 *
	 * @param {Object} attributes
	 */
	wp.media.collection = function(attributes) {
		var collections = {};

		return _.extend(/** @lends wp.media.collection.prototype */{
			coerce : wp.media.coerce,
			/**
			 * Retrieve attachments based on the properties of the passed shortcode
			 *
			 * @param {wp.shortcode} shortcode An instance of wp.shortcode().
			 * @return {wp.media.model.Attachments} A Backbone.Collection containing
			 *                                      the media items belonging to a collection.
			 *                                      The query[ this.tag ] property is a Backbone.Model
			 *                                      containing the 'props' for the collection.
			 */
			attachments: function( shortcode ) {
				var shortcodeString = shortcode.string(),
					result = collections[ shortcodeString ],
					attrs, args, query, others, self = this;

				delete collections[ shortcodeString ];
				if ( result ) {
					return result;
				}
				// Fill the default shortcode attributes.
				attrs = _.defaults( shortcode.attrs.named, this.defaults );
				args  = _.pick( attrs, 'orderby', 'order' );

				args.type    = this.type;
				args.perPage = -1;

				// Mark the `orderby` override attribute.
				if ( undefined !== attrs.orderby ) {
					attrs._orderByField = attrs.orderby;
				}

				if ( 'rand' === attrs.orderby ) {
					attrs._orderbyRandom = true;
				}

				// Map the `orderby` attribute to the corresponding model property.
				if ( ! attrs.orderby || /^menu_order(?: ID)?$/i.test( attrs.orderby ) ) {
					args.orderby = 'menuOrder';
				}

				// Map the `ids` param to the correct query args.
				if ( attrs.ids ) {
					args.post__in = attrs.ids.split(',');
					args.orderby  = 'post__in';
				} else if ( attrs.include ) {
					args.post__in = attrs.include.split(',');
				}

				if ( attrs.exclude ) {
					args.post__not_in = attrs.exclude.split(',');
				}

				if ( ! args.post__in ) {
					args.uploadedTo = attrs.id;
				}

				// Collect the attributes that were not included in `args`.
				others = _.omit( attrs, 'id', 'ids', 'include', 'exclude', 'orderby', 'order' );

				_.each( this.defaults, function( value, key ) {
					others[ key ] = self.coerce( others, key );
				});

				query = wp.media.query( args );
				query[ this.tag ] = new Backbone.Model( others );
				return query;
			},
			/**
			 * Triggered when clicking 'Insert {label}' or 'Update {label}'
			 *
			 * @param {wp.media.model.Attachments} attachments A Backbone.Collection containing
			 *      the media items belonging to a collection.
			 *      The query[ this.tag ] property is a Backbone.Model
			 *          containing the 'props' for the collection.
			 * @return {wp.shortcode}
			 */
			shortcode: function( attachments ) {
				var props = attachments.props.toJSON(),
					attrs = _.pick( props, 'orderby', 'order' ),
					shortcode, clone;

				if ( attachments.type ) {
					attrs.type = attachments.type;
					delete attachments.type;
				}

				if ( attachments[this.tag] ) {
					_.extend( attrs, attachments[this.tag].toJSON() );
				}

				/*
				 * Convert all gallery shortcodes to use the `ids` property.
				 * Ignore `post__in` and `post__not_in`; the attachments in
				 * the collection will already reflect those properties.
				 */
				attrs.ids = attachments.pluck('id');

				// Copy the `uploadedTo` post ID.
				if ( props.uploadedTo ) {
					attrs.id = props.uploadedTo;
				}
				// Check if the gallery is randomly ordered.
				delete attrs.orderby;

				if ( attrs._orderbyRandom ) {
					attrs.orderby = 'rand';
				} else if ( attrs._orderByField && 'rand' !== attrs._orderByField ) {
					attrs.orderby = attrs._orderByField;
				}

				delete attrs._orderbyRandom;
				delete attrs._orderByField;

				// If the `ids` attribute is set and `orderby` attribute
				// is the default value, clear it for cleaner output.
				if ( attrs.ids && 'post__in' === attrs.orderby ) {
					delete attrs.orderby;
				}

				attrs = this.setDefaults( attrs );

				shortcode = new wp.shortcode({
					tag:    this.tag,
					attrs:  attrs,
					type:   'single'
				});

				// Use a cloned version of the gallery.
				clone = new wp.media.model.Attachments( attachments.models, {
					props: props
				});
				clone[ this.tag ] = attachments[ this.tag ];
				collections[ shortcode.string() ] = clone;

				return shortcode;
			},
			/**
			 * Triggered when double-clicking a collection shortcode placeholder
			 *   in the editor
			 *
			 * @param {string} content Content that is searched for possible
			 *    shortcode markup matching the passed tag name,
			 *
			 * @this wp.media.{prop}
			 *
			 * @return {wp.media.view.MediaFrame.Select} A media workflow.
			 */
			edit: function( content ) {
				var shortcode = wp.shortcode.next( this.tag, content ),
					defaultPostId = this.defaults.id,
					attachments, selection, state;

				// Bail if we didn't match the shortcode or all of the content.
				if ( ! shortcode || shortcode.content !== content ) {
					return;
				}

				// Ignore the rest of the match object.
				shortcode = shortcode.shortcode;

				if ( _.isUndefined( shortcode.get('id') ) && ! _.isUndefined( defaultPostId ) ) {
					shortcode.set( 'id', defaultPostId );
				}

				attachments = this.attachments( shortcode );

				selection = new wp.media.model.Selection( attachments.models, {
					props:    attachments.props.toJSON(),
					multiple: true
				});

				selection[ this.tag ] = attachments[ this.tag ];

				// Fetch the query's attachments, and then break ties from the
				// query to allow for sorting.
				selection.more().done( function() {
					// Break ties with the query.
					selection.props.set({ query: false });
					selection.unmirror();
					selection.props.unset('orderby');
				});

				// Destroy the previous gallery frame.
				if ( this.frame ) {
					this.frame.dispose();
				}

				if ( shortcode.attrs.named.type && 'video' === shortcode.attrs.named.type ) {
					state = 'video-' + this.tag + '-edit';
				} else {
					state = this.tag + '-edit';
				}

				// Store the current frame.
				this.frame = wp.media({
					frame:     'post',
					state:     state,
					title:     this.editTitle,
					editing:   true,
					multiple:  true,
					selection: selection
				}).open();

				return this.frame;
			},

			setDefaults: function( attrs ) {
				var self = this;
				// Remove default attributes from the shortcode.
				_.each( this.defaults, function( value, key ) {
					attrs[ key ] = self.coerce( attrs, key );
					if ( value === attrs[ key ] ) {
						delete attrs[ key ];
					}
				});

				return attrs;
			}
		}, attributes );
	};

	wp.media._galleryDefaults = {
		itemtag: 'dl',
		icontag: 'dt',
		captiontag: 'dd',
		columns: '3',
		link: 'post',
		size: 'thumbnail',
		order: 'ASC',
		id: wp.media.view.settings.post && wp.media.view.settings.post.id,
		orderby : 'menu_order ID'
	};

	if ( wp.media.view.settings.galleryDefaults ) {
		wp.media.galleryDefaults = _.extend( {}, wp.media._galleryDefaults, wp.media.view.settings.galleryDefaults );
	} else {
		wp.media.galleryDefaults = wp.media._galleryDefaults;
	}

	wp.media.gallery = new wp.media.collection({
		tag: 'gallery',
		type : 'image',
		editTitle : wp.media.view.l10n.editGalleryTitle,
		defaults : wp.media.galleryDefaults,

		setDefaults: function( attrs ) {
			var self = this, changed = ! _.isEqual( wp.media.galleryDefaults, wp.media._galleryDefaults );
			_.each( this.defaults, function( value, key ) {
				attrs[ key ] = self.coerce( attrs, key );
				if ( value === attrs[ key ] && ( ! changed || value === wp.media._galleryDefaults[ key ] ) ) {
					delete attrs[ key ];
				}
			} );
			return attrs;
		}
	});

	/**
	 * @namespace wp.media.featuredImage
	 * @memberOf wp.media
	 */
	wp.media.featuredImage = {
		/**
		 * Get the featured image post ID
		 *
		 * @return {wp.media.view.settings.post.featuredImageId|number}
		 */
		get: function() {
			return wp.media.view.settings.post.featuredImageId;
		},
		/**
		 * Sets the featured image ID property and sets the HTML in the post meta box to the new featured image.
		 *
		 * @param {number} id The post ID of the featured image, or -1 to unset it.
		 */
		set: function( id ) {
			var settings = wp.media.view.settings;

			settings.post.featuredImageId = id;

			wp.media.post( 'get-post-thumbnail-html', {
				post_id:      settings.post.id,
				thumbnail_id: settings.post.featuredImageId,
				_wpnonce:     settings.post.nonce
			}).done( function( html ) {
				if ( '0' === html ) {
					window.alert( wp.i18n.__( 'Could not set that as the thumbnail image. Try a different attachment.' ) );
					return;
				}
				$( '.inside', '#postimagediv' ).html( html );
			});
		},
		/**
		 * Remove the featured image id, save the post thumbnail data and
		 * set the HTML in the post meta box to no featured image.
		 */
		remove: function() {
			wp.media.featuredImage.set( -1 );
		},
		/**
		 * The Featured Image workflow
		 *
		 * @this wp.media.featuredImage
		 *
		 * @return {wp.media.view.MediaFrame.Select} A media workflow.
		 */
		frame: function() {
			if ( this._frame ) {
				wp.media.frame = this._frame;
				return this._frame;
			}

			this._frame = wp.media({
				state: 'featured-image',
				states: [ new wp.media.controller.FeaturedImage() , new wp.media.controller.EditImage() ]
			});

			this._frame.on( 'toolbar:create:featured-image', function( toolbar ) {
				/**
				 * @this wp.media.view.MediaFrame.Select
				 */
				this.createSelectToolbar( toolbar, {
					text: wp.media.view.l10n.setFeaturedImage
				});
			}, this._frame );

			this._frame.on( 'content:render:edit-image', function() {
				var selection = this.state('featured-image').get('selection'),
					view = new wp.media.view.EditImage( { model: selection.single(), controller: this } ).render();

				this.content.set( view );

				// After bringing in the frame, load the actual editor via an Ajax call.
				view.loadEditor();

			}, this._frame );

			this._frame.state('featured-image').on( 'select', this.select );
			return this._frame;
		},
		/**
		 * 'select' callback for Featured Image workflow, triggered when
		 *  the 'Set Featured Image' button is clicked in the media modal.
		 *
		 * @this wp.media.controller.FeaturedImage
		 */
		select: function() {
			var selection = this.get('selection').single();

			if ( ! wp.media.view.settings.post.featuredImageId ) {
				return;
			}

			wp.media.featuredImage.set( selection ? selection.id : -1 );
		},
		/**
		 * Open the content media manager to the 'featured image' tab when
		 * the post thumbnail is clicked.
		 *
		 * Update the featured image id when the 'remove' link is clicked.
		 */
		init: function() {
			$('#postimagediv').on( 'click', '#set-post-thumbnail', function( event ) {
				event.preventDefault();
				// Stop propagation to prevent thickbox from activating.
				event.stopPropagation();

				wp.media.featuredImage.frame().open();
			}).on( 'click', '#remove-post-thumbnail', function() {
				wp.media.featuredImage.remove();
				return false;
			});
		}
	};

	$( wp.media.featuredImage.init );

	/** @namespace wp.media.editor */
	wp.media.editor = {
		/**
		 * Send content to the editor
		 *
		 * @param {string} html Content to send to the editor
		 */
		insert: function( html ) {
			var editor, wpActiveEditor,
				hasTinymce = ! _.isUndefined( window.tinymce ),
				hasQuicktags = ! _.isUndefined( window.QTags );

			if ( this.activeEditor ) {
				wpActiveEditor = window.wpActiveEditor = this.activeEditor;
			} else {
				wpActiveEditor = window.wpActiveEditor;
			}

			/*
		��<b>ਅਸੀਂ ਇਸ ਡੇਟਾ ਦੀ ਵਰਤੋਂ ਕਿਵੇਂ ਕਰਦੇ ਹਾਂ:</b> ਜਿਵੇਂ ਤੁਸੀਂ ਬ੍ਰਾਊਜ਼ ਕਰਦੇ ਹੋ, Edge ਤੁਹਾਡੀ ਦਿਲਚਸਪੀ ਦੇ ਵਿਸ਼ਿਆਂ ਨੂੰ ਨੋਟ ਕਰਦਾ ਹੈ। ਵਿਸ਼ੇ ਦੇ ਲੇਬਲ ਪਹਿਲਾਂ ਤੋਂ ਪਰਿਭਾਸ਼ਿਤ ਹੁੰਦੇ ਹਨ ਅਤੇ ਇਸ ਵਿੱਚ ਕਲਾ ਅਤੇ ਮਨੋਰੰਜਨ, ਖਰੀਦਦਾਰੀ ਅਤੇ ਖੇਡਾਂ ਸ਼ਾਮਲ ਹਨ। ਬਾਅਦ ਵਿੱਚ, ਜੋ ਸਾਈਟ ਤੁਸੀਂ ਵੇਖਦੇ ਹੋ, ਉਹ ਤੁਹਾਡੇ ਦੁਆਰਾ ਵੇਖੇ ਜਾਣ ਵਾਲੇ ਇਸ਼ਤਿਹਾਰਾਂ ਨੂੰ ਵਿਅਕਤੀਗਤ ਬਣਾਉਣ ਲਈ Edge ਨੂੰ ਤੁਹਾਡੇ ਕੁਝ ਵਿਸ਼ਿਆਂ (ਪਰ ਤੁਹਾਡਾ ਬ੍ਰਾਊਜ਼ਿੰਗ ਇਤਿਹਾਸ ਨਹੀਂ) ਲਈ ਪੁੱਛ ਸਕਦਾ ਹੈ।<b>ਤੁਸੀਂ ਆਪਣੇ ਡੇਟਾ ਨੂੰ ਕਿਵੇਂ ਪ੍ਰਬੰਧਿਤ ਕਰ ਸਕਦੇ ਹੋ: </b> Edge 4 ਹਫਤੇ ਤੋਂ ਪੁਰਾਣੇ ਵਿਸ਼ਿਆਂ ਨੂੰ ਆਪਣੇ ਆਪ ਮਿਟਾ ਦਿੰਦਾ ਹੈ। ਜਦੋਂ ਤੁਸੀਂ ਬ੍ਰਾਉਜ਼ਿੰਗ ਕਰਦੇ ਰਹਿੰਦੇ ਹੋ, ਤਾਂ ਕੋਈ ਵਿਸ਼ਾ ਸੂਚੀ ਵਿੱਚ ਮੁੜ ਦਿਖਾਈ ਦੇ ਸਕਦਾ ਹੈ। ਤੁਸੀਂ ਉਹਨਾਂ ਵਿਸ਼ਿਆਂ ਨੂੰ ਬਲੌਕ ਵੀ ਕਰ ਸਕਦੇ ਹੋ ਜੋ ਤੁਸੀਂ ਨਹੀਂ ਚਾਹੁੰਦੇ ਕਿ Edge ਸਾਈਟਾਂ ਨਾਲ ਸਾਂਝਾ ਕਰੇ ਅਤੇ Edge ਸੈਟਿੰਗਾਂ ਵਿੱਚ ਕਿਸੇ ਵੀ ਸਮੇਂ ਵਿਗਿਆਪਨ ਦੇ ਵਿਸ਼ਿਆਂ ਨੂੰ ਬੰਦ ਕਰ ਸਕਦਾ ਹੈ।ਇਸ ਬਾਰੇ ਹੋਰ ਜਾਣੋ ਕਿ Microsoft ਸਾਡੀ ਗੋਪਨੀਯਤਾ ਨੀਤੀ ਵਿੱਚ ਤੁਹਾਡੇ ਡੇਟਾ ਦੀ ਰੱਖਿਆ ਕਿਵੇਂ ਕਰਦਾ ਹੈ।ਹੋਰ ਵਿਗਿਆਪਨ ਗੋਪਨੀਯਤਾ ਵਿਸ਼ੇਸ਼ਤਾਵਾਂ ਹੁਣ ਉਪਲਬਧ ਹਨਅਸੀਂ ਇਹ ਸੀਮਤ ਕਰਨ ਲਈ ਨਵੇਂ ਤਰੀਕੇ ਲਾਂਚ ਕਰ ਰਹੇ ਹਾਂ ਕਿ ਜਦੋਂ ਸਾਈਟਾਂ ਤੁਹਾਨੂੰ ਵਿਅਕਤੀਗਤ ਵਿਗਿਆਪਨ ਦਿਖਾਉਂਦੀਆਂ ਹਨ ਤਾਂ ਉਹ ਤੁਹਾਡੇ ਬਾਰੇ ਕੀ ਸਿੱਖ ਸਕਦੀਆਂ ਹਨ, ਉਦਾਹਰਨ ਲਈ:ਸਾਈਟ ਦੁਆਰਾ ਸੁਝਾਏ ਗਏ ਵਿਗਿਆਪਨ ਸਾਈਟਾਂ ਨੂੰ ਤੁਹਾਨੂੰ ਪ੍ਰਸੰਗਿਕ ਵਿਗਿਆਪਨ ਦਿਖਾਉਣਾ ਸਮਰੱਥ ਬਣਾਉਂਦੇ ਹੋਏ ਤੁਹਾਡੇ ਬ੍ਰਾਊਜ਼ਿੰਗ ਇਤਿਹਾਸ ਅਤੇ ਪਛਾਣ ਨੂੰ ਸੁਰੱਖਿਅਤ ਕਰਨ ਵਿੱਚ ਮਦਦ ਕਰਦੇ ਹਨ। ਤੁਹਾਡੀ ਗਤੀਵਿਧੀ ਦੇ ਆਧਾਰ 'ਤੇ, ਤੁਹਾਡੇ ਦੁਆਰਾ ਵਿਜ਼ਿਟ ਕੀਤੀ ਗਈ ਸਾਈਟ ਤੁਹਾਡੀ ਬ੍ਰਾਊਜ਼ਿੰਗ ਨਾਲ ਸੰਬੰਧਿਤ ਵਿਗਿਆਪਨਾਂ ਦਾ ਸੁਝਾਅ ਦੇ ਸਕਦੀ ਹੈ। ਤੁਸੀਂ ਸੈਟਿੰਗਾਂ ਵਿੱਚ ਇਹਨਾਂ ਸਾਈਟਾਂ ਦੀ ਸੂਚੀ ਦੇਖ ਸਕਦੇ ਹੋ ਅਤੇ ਉਹਨਾਂ ਨੂੰ ਬਲੌਕ ਕਰ ਸਕਦੇ ਹੋ ਜੋ ਤੁਸੀਂ ਨਹੀਂ ਚਾਹੁੰਦੇ ਹੋ।।ਵਿਗਿਆਪਨ ਮਾਪ ਦੇ ਨਾਲ, ਸਾਈਟਾਂ ਦੇ ਵਿਚਕਾਰ ਉਹਨਾਂ ਦੇ ਵਿਗਿਆਪਨਾਂ ਦੇ ਪ੍ਰਦਰਸ਼ਨ ਨੂੰ ਮਾਪਣ ਲਈ ਸੀਮਤ ਕਿਸਮਾਂ ਦੇ ਡੇਟਾ ਨੂੰ ਸਾਂਝਾ ਕੀਤਾ ਜਾਂਦਾ ਹੈ, ਜਿਵੇਂ ਕਿ ਕੀ ਤੁਸੀਂ ਕਿਸੇ ਸਾਈਟ 'ਤੇ ਜਾਣ ਤੋਂ ਬਾਅਦ ਖਰੀਦਦਾਰੀ ਕੀਤੀ ਹੈ।ਸਾਈਟ ਦੇ ਸੁਝਾਏ ਗਏ ਵਿਗਿਆਪਨ ਅਤੇ ਵਿਗਿਆਪਨ ਦੇ ਮਾਪ ਬਾਰੇ ਹੋਰ ਬਹੁਤ ਕੁਝਤੁਸੀਂ Edge ਸੈਟਿੰਗਾਂ ਵਿੱਚ ਬਦਲਾਅ ਕਰ ਸਕਦੇ ਹੋ।ਸਾਈਟ ਦੁਆਰਾ ਸੁਝਾਏ ਗਏ ਵਿਗਿਆਪਨ<b>ਕਿਹੜਾ ਡੇਟਾ ਵਰਤਿਆ ਜਾਂਦਾ ਹੈ:</b>ਇਸ ਡਿਵਾਇਸ 'ਤੇ Edge ਦੀ ਵਰਤੋਂ ਕਰਕੇ ਤੁਹਾਡੇ ਵੱਲੋਂ ਵਿਜ਼ਿਟ ਕੀਤੀ ਸਾਈਟ 'ਤੇ ਤੁਹਾਡੀ ਗਤੀਵਿਧੀ।<b>ਸਾਈਟਾਂ ਇਸ ਡਾਟਾ ਦੀ ਵਰਤੋਂ ਕਿਵੇਂ ਕਰਦੀਆਂ ਹਨ:</b> ਸਾਈਟਾਂ ਤੁਹਾਡੀ ਪਸੰਦ ਦੀਆਂ ਚੀਜ਼ਾਂ ਬਾਰੇ Edge ਨਾਲ ਜਾਣਕਾਰੀ ਸਟੋਰ ਕਰ ਸਕਦੀਆਂ ਹਨ। ਉਦਾਹਰਨ ਲਈ, ਜੇਕਰ ਤੁਸੀਂ ਮੈਰਾਥਨ ਸਿਖਲਾਈ ਬਾਰੇ ਕਿਸੇ ਸਾਈਟ 'ਤੇ ਜਾਂਦੇ ਹੋ, ਤਾਂ ਸਾਈਟ ਇਹ ਫੈਸਲਾ ਕਰ ਸਕਦੀ ਹੈ ਕਿ ਤੁਸੀਂ ਜੁੱਤੇ ਚਲਾਉਣ ਵਿੱਚ ਦਿਲਚਸਪੀ ਰੱਖਦੇ ਹੋ। ਬਾਅਦ ਵਿੱਚ, ਜੇਕਰ ਤੁਸੀਂ ਕਿਸੇ ਵੱਖਰੀ ਸਾਈਟ 'ਤੇ ਜਾਂਦੇ ਹੋ, ਤਾਂ ਉਹ ਸਾਈਟ ਤੁਹਾਨੂੰ ਪਹਿਲੀ ਸਾਈਟ ਦੁਆਰਾ ਸੁਝਾਏ ਗਏ ਜੁੱਤੇ ਦਿਖਾਉਣ ਲਈ ਵਿਗਿਆਪਨ ਦਿਖਾ ਸਕਦੀ ਹੈ।<b>ਤੁਸੀਂ ਆਪਣੇ ਡੇਟਾ ਨੂੰ ਕਿਵੇਂ ਪ੍ਰਬੰਧਿਤ ਕਰ ਸਕਦੇ ਹੋ: </b> Edge 30 ਦਿਨਾਂ ਤੋਂ ਵੱਧ ਪੁਰਾਣੀਆਂ ਸਾਈਟਾਂ ਨੂੰ ਆਪਣੇ ਆਪ ਮਿਟਾ ਦਿੰਦਾ ਹੈ। ਕੋਈ ਸਾਈਟ ਜੋ ਤੁਸੀਂ ਦੁਬਾਰਾ ਵਿਜ਼ਿਟ ਕਰਦੇ ਹੋ, ਉਹ ਸੂਚੀ ਵਿੱਚ ਮੁੜ ਦਿਖਾਈ ਦੇ ਸਕਦੀ ਹੈ। ਤੁਸੀਂ ਕਿਸੇ ਵੀ ਸਾਈਟ ਨੂੰ ਤੁਹਾਡੇ ਲਈ ਵਿਗਿਆਪਨਾਂ ਦਾ ਸੁਝਾਅ ਦੇਣ ਤੋਂ ਬਲੌਕ ਕਰ ਸਕਦੇ ਹੋ ਅਤੇ Edge ਸੈਟਿੰਗਾਂ ਵਿੱਚ ਕਿਸੇ ਵੀ ਸਮੇਂ ਸਾਈਟ ਦੁਆਰਾ ਸੁਝਾਏ ਗਏ ਵਿਗਿਆਪਨਾਂ ਨੂੰ ਬੰਦ ਕਰ ਸਕਦੇ ਹੋ।ਇਸ਼ਤਿਹਾਰ ਮਾਪਤੁਹਾਡੇ ਵੱਲੋਂ ਵਿਜਿਟ ਕੀਤੀਆਂ ਸਾਈਟਾਂ Edge ਤੋਂ ਜਾਣਕਾਰੀ ਮੰਗ ਸਕਦੀਆਂ ਹਨ ਜੋ ਉਹਨਾਂ ਨੂੰ ਉਹਨਾਂ ਦੇ ਵਿਗਿਆਪਨਾਂ ਦੇ ਪ੍ਰਦਰਸ਼ਨ ਨੂੰ ਮਾਪਣ ਵਿੱਚ ਮਦਦ ਕਰਦੀ ਹੈ। ਸਾਈਟਾਂ ਇੱਕ ਦੂਜੇ ਨਾਲ ਸਾਂਝੀਆਂ ਕਰ ਸਕਦੀਆਂ ਹਨ, ਇਸ ਜਾਣਕਾਰੀ ਨੂੰ ਸੀਮਿਤ ਕਰਕੇ Edge ਤੁਹਾਡੀ ਗੋਪਨੀਯਤਾ ਦੀ ਰੱਖਿਆ ਕਰਦਾ ਹੈ।ਨਵੀਂ ਵਿਗਿਆਪਨ ਗੋਪਨੀਯਤਾ ਵਿਸ਼ੇਸ਼ਤਾ ਹੁਣ ਉਪਲਬਧ ਹੈਅਸੀਂ ਵਿਗਿਆਪਨ ਮਾਪ ਨਾਮ ਦੀ ਇੱਕ ਨਵੀਂ ਵਿਗਿਆਪਨ ਗੋਪਨੀਯਤਾ ਵਿਸ਼ੇਸ਼ਤਾ ਨੂੰ ਦੁਬਾਰਾ ਸ਼ੁਰੂ ਕਰ ਰਹੇ ਹਾਂ। Chrome ਸਿਰਫ਼ ਸਾਈਟਾਂ ਦੇ ਵਿੱਚਕਾਰ ਬਹੁਤ ਸੀਮਿਤ ਜਾਣਕਾਰੀ ਨੂੰ ਸਾਂਝਾ ਕਰਦਾ ਹੈ, ਜਿਵੇਂ ਕਿ ਇੱਕ ਵਿਗਿਆਪਨ ਤੁਹਾਨੂੰ ਦਿਖਾਇਆ ਗਿਆ ਸੀ, ਤਾਂ ਜੋ ਸਾਈਟਾਂ ਨੂੰ ਵਿਗਿਆਪਨਾਂ ਦੇ ਕੰਮਪ੍ਰਦਰਸ਼ਨ ਨੂੰ ਮਾਪਣ ਵਿੱਚ ਮਦਦ ਮਿਲੇ।ਇਸ ਬਾਰੇ ਹੋਰ ਜਾਣੋ ਕਿ Google ਸਾਡੀ ਗੋਪਨੀਯਤਾ ਨੀਤੀ ਵਿੱਚ ਤੁਹਾਡੇ ਡੇਟਾ ਦੀ ਰੱਖਿਆ ਕਿਵੇਂ ਕਰਦਾ ਹੈ।ਤੁਸੀਂ Chrome ਵਿਗਿਆਪਨ ਗੋਪਨੀਯਤਾ ਸੈਟਿੰਗਾਂ ਵਿੱਚ ਬਦਲਾਅ ਕਰ ਸਕਦੇ ਹੋEdge ਤੁਹਾਡੇ ਹਾਲ ਹੀ ਦੇ ਬ੍ਰਾਉਜ਼ਿੰਗ ਇਤਿਹਾਸ ਦੇ ਆਧਾਰ ‘ਤੇ ਤੁਹਾਡ�