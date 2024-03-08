/**
 * repr :: gets the string representation of `arg`
 * @param {} arg :: unknown function argument
 * @returns {String} :: a string representation of `arg`
 */
export const repr = (arg) => {
	return Object.prototype.toString.call(arg);
};

/**
 * isArray
 * @param {} arg :: unknown function argument
 * @returns {Boolean} :: returns true if `arg` is an Array, false otherwise
 */
export const isArray = (arg) => {
	return Array.isArray ? Array.isArray(arg) : repr(arg) === "[object Array]";
};

/**
 * isObject :: checks if `arg` is an object.
 * @param {} arg :: unknown function argument
 * @returns {Boolean} :: returns true if `arg` is an object.
 */
export const isObject = (arg) => {
	return repr(arg) === "[object Object]";
};

/**
 * isString :: checks if `arg` is a string.
 * @param {} arg :: unknown function argument
 * @returns {Boolean} :: returns true if `arg` is a String, false otherwise
 */
export const isString = (arg) => {
	return repr(arg) === "[object String]";
};

/**
 * isNumber :: checks if `arg` is a number.
 * @param {} arg :: unknown function argument
 * @returns {Boolean} :: returns true if `arg` is a Number, false otherwise
 */
export const isNumber = (arg) => {
	return repr(arg) === "[object Number]";
};

export const isFloat = (n) => {
	return Number(n) === n && n % 1 !== 0;
};

/**
 * isNull :: checks if `arg` is null.
 * @param {} arg :: unknown function argument
 * @returns {Boolean} :: returns true if `arg` is of type Null, false otherwise
 */
export const isNull = (arg) => {
	return repr(arg) === "[object Null]";
};

/**
 * isUndefined :: checks if `arg` is undefined.
 * @param {} arg :: unknown function argument
 * @returns {Boolean} :: Returns true if `arg` is of type Undefined, false otherwise
 */
export const isUndefined = (arg) => {
	try {
		return typeof arg === "undefined";
	} catch (e) {
		if (e instanceof ReferenceError) {
			return true;
		}

		throw e;
	}
};

/**
 * isEmpty :: Checks if `arg` is an empty string, array, or object.
 *
 * @param {} arg :: unknown function argument
 * @returns {Boolean} :: Returns true if `arg` is an empty string,
 *  array, or object. Also returns true is `arg` is null or
 *  undefined. Returns true otherwise.
 */
export const isEmpty = (arg) => {
	return (
		isUndefined(arg) ||
		isNull(arg) ||
		(isString(arg) && arg.length === 0) ||
		(isArray(arg) && arg.length === 0) ||
		(isObject(arg) && Object.keys(arg).length === 0)
	);
};
