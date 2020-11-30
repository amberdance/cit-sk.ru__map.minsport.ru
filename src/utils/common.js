export const dateHelper = (format = "yy-mm-dd hh:mm:ss") => {
  let curentDate = new Date(),
    y = curentDate.getFullYear(),
    m = curentDate.getMonth() + 1,
    d = curentDate.getDate(),
    h = curentDate.getHours(),
    mm = curentDate.getMinutes(),
    s = curentDate.getSeconds();

  if (format == "yy-mm-dd hh:mm:ss") {
    return `${y}-${m < 10 ? "0" + m : m}-${d < 10 ? "0" + d : d} ${h}:${
      mm < 10 ? "0" + mm : mm
    }:${s < 10 ? "0" + s : s}`;
  }

  if (format == "dd.mm.yyyy hh:mm") {
    return `${d < 10 ? "0" + d : d}.${m < 10 ? "0" + m : m}.${y} ${h}:${
      mm < 10 ? "0" + mm : mm
    }`;
  }
};

export const dataSet = (destinationObject, sourceObject) => {
  let result = destinationObject;

  for (let prop in sourceObject) {
    result[prop] = sourceObject[prop];
  }

  return result;
};

export const formValidateHelper = {
  checkNumber: (rule, val, callback) => {
    if (val == "") return callback(new Error("Введите инвентарный номер"));

    if (!Number.isInteger(+val))
      return callback(new Error("Допускаются только цифровые значения"));

    if (val.length > 6 || val.length < 6) {
      return callback(
        new Error("Инвентарный номер должен иметь шесть символов")
      );
    }
    callback();
  },

  isPositiveNumber: (rule, val, callback) => {
    if (Number(val) < 0)
      return callback(new Error("Допускаются только положительные значения"));

    if (Number(val) == 0)
      return callback(new Error("Укажите количество товаров"));

    if (val.length > 2 || Number(val) >= 50)
      return callback("Запрошено много позиций");

    callback();
  },
};

export const throttle = (func, ms) => {
  let isThrottled = false,
    savedArgs,
    savedThis;

  function wrapper() {
    if (isThrottled) {
      savedArgs = arguments;
      savedThis = this;
      return;
    }

    func.apply(this, arguments);

    isThrottled = true;

    setTimeout(function() {
      isThrottled = false;
      if (savedArgs) {
        wrapper.apply(savedThis, savedArgs);
        savedArgs = savedThis = null;
      }
    }, ms);
  }

  return wrapper;
};

export const getPayloadData = (object) => {
  let payload = {};

  for (const key in object) {
    if (object.hasOwnProperty(key)) {
      const prop = object[key];

      if (prop) {
        payload[key] = prop;
      }
    }
  }

  return payload;
};

export const isEmptyObject = (object) => {
  for (let key in object) {
    if (object.hasOwnProperty(key)) {
      return false;
    }
  }

  return true;
};

export const removeEmptyFields = (object) => {
  let result = {};

  Object.keys(object).forEach((key) => {
    if (object[key] !== null && Object.keys(object[key]).length)
      result[key] = object[key];
  });

  return result;
};
