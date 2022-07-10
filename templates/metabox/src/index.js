import Table from "./components/table";

/**
 * Get root element.
 * 
 * @param  DOMString  elementId
 * @return Element
 * 
 * @see https://developer.mozilla.org/pl/docs/Web/API/Document/getElementById
 */
const root = document.getElementById(props.id);

/**
 * Render component.
 * 
 * @param element
 * @param container
 * 
 * @see https://pl.reactjs.org/docs/react-dom.html#render
 */
wp.element.render(<Table meta={props.meta}/>, root);