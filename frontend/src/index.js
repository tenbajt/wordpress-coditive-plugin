import Form from "./components/form";

/**
 * Get the root element.
 * 
 * @param  DOMString  elementId
 * @return Element
 * 
 * @see https://developer.mozilla.org/pl/docs/Web/API/Document/getElementById
 */
const root = document.getElementById(props.id);

/**
 * Render the form.
 * 
 * @param element
 * @param container
 * 
 * @see https://pl.reactjs.org/docs/react-dom.html#render
 */
wp.element.render(<Form url={props.url} nonce={props.nonce} action={props.action}/>, root);