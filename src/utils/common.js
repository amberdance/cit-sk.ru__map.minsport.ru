export const dateHelper = (format = 'yy-mm-dd hh:mm:ss') => {
  const curentDate = new Date()
  const y = curentDate.getFullYear()
  const m = curentDate.getMonth() + 1
  const d = curentDate.getDate()
  const h = curentDate.getHours()
  const mm = curentDate.getMinutes()
  const s = curentDate.getSeconds()

  if (format === 'yy-mm-dd hh:mm:ss') {
    return `${y}-${m < 10 ? '0' + m : m}-${d < 10 ? '0' + d : d} ${h}:${
      mm < 10 ? '0' + mm : mm
    }:${s < 10 ? '0' + s : s}`
  }

  if (format === 'dd.mm.yyyy hh:mm') {
    return `${d < 10 ? '0' + d : d}.${m < 10 ? '0' + m : m}.${y} ${h}:${
      mm < 10 ? '0' + mm : mm
    }`
  }
}

export const dataSet = (destinationObject, sourceObject) => {
  const result = destinationObject

  for (const prop in sourceObject) {
    result[prop] = sourceObject[prop]
  }

  return result
}

export const throttle = (func, ms) => {
  let isThrottled = false
  let savedArgs
  let savedThis

  function wrapper () {
    if (isThrottled) {
      savedArgs = arguments
      savedThis = this
      return
    }

    func.apply(this, arguments)

    isThrottled = true

    setTimeout(function () {
      isThrottled = false
      if (savedArgs) {
        wrapper.apply(savedThis, savedArgs)
        savedArgs = savedThis = null
      }
    }, ms)
  }

  return wrapper
}

export const getPayloadData = (object) => {
  const payload = {}

  for (const key in object) {
    if (object.hasOwnProperty(key)) {
      const prop = object[key]

      if (prop) {
        payload[key] = prop
      }
    }
  }

  return payload
}

export const isEmptyObject = (object) => {
  for (const key in object) {
    if (object.hasOwnProperty(key)) {
      return false
    }
  }

  return true
}

export const removeEmptyFields = (object) => {
  const result = {}

  Object.keys(object).forEach((key) => {
    if (object[key] !== null && Object.keys(object[key]).length) { result[key] = object[key] }
  })

  return result
}
