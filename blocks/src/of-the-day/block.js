/**
 * BLOCK: last-death
 * @since 1.1.0
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { ServerSideRender, SelectControl, PanelBody } = wp.components;
const { InspectorControls } = wp.editor;
const { Fragment } = wp.element;

/**
 * Register: Gutenberg Block.
 */
registerBlockType( 'lezwatchtv/of-the-day', {

	title: __( '... Of The Day (LezWatchTV)' ),
	// https://fontawesome.com/icons/birthday-cake?style=solid
	icon: <svg aria-hidden="true" data-prefix="fas" data-icon="birthday-cake" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-birthday-cake fa-w-14 fa-3x"><path fill="currentColor" d="M448 384c-28.02 0-31.26-32-74.5-32-43.43 0-46.825 32-74.75 32-27.695 0-31.454-32-74.75-32-42.842 0-47.218 32-74.5 32-28.148 0-31.202-32-74.75-32-43.547 0-46.653 32-74.75 32v-80c0-26.5 21.5-48 48-48h16V112h64v144h64V112h64v144h64V112h64v144h16c26.5 0 48 21.5 48 48v80zm0 128H0v-96c43.356 0 46.767-32 74.75-32 27.951 0 31.253 32 74.75 32 42.843 0 47.217-32 74.5-32 28.148 0 31.201 32 74.75 32 43.357 0 46.767-32 74.75-32 27.488 0 31.252 32 74.5 32v96zM96 96c-17.75 0-32-14.25-32-32 0-31 32-23 32-64 12 0 32 29.5 32 56s-14.25 40-32 40zm128 0c-17.75 0-32-14.25-32-32 0-31 32-23 32-64 12 0 32 29.5 32 56s-14.25 40-32 40zm128 0c-17.75 0-32-14.25-32-32 0-31 32-23 32-64 12 0 32 29.5 32 56s-14.25 40-32 40z" class=""></path></svg>,
	category: 'lezwatchtv',
	keywords: [
		__( 'lezwatchtv' ),
		__( 'character of the day' ),
		__( 'show of the day' ),
	],
	align: true,
	className: false,
	attributes: {
		otd: {
			type: 'string',
			default: 'character',
		}
	},
	html: false,

	/**
	 * Editor View
	 */
	edit: props => {

		const { attributes: { placeholder }, setAttributes } = props;
		getAuthors

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ '... Of The Day Settings' }>
						<SelectControl
							type='string'
							label={ 'Content Type' }
							value={ props.attributes.otd }
							onChange={ ( value ) => props.setAttributes( { otd: value } ) }
							options={ [
								{ label: __( 'Character' ), value: 'character' },
								{ label: __( 'TV Show' ), value: 'show' },
								{ label: __( 'Character Death' ), value: 'death' },
								{ label: __( 'Actor Birthday' ), value: 'birthday' },
							] }
						/>
					</PanelBody>
				</InspectorControls>
				<ServerSideRender
					block='lezwatchtv/of-the-day'
					attributes={ props.attributes }
				/>
			</Fragment>
		);
	},

	/**
	 * Front End View
	 */
	save() {
		// Rendering in PHP
		return null;
	},
} );
