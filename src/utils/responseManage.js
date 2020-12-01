/* eslint-disable prefer-promise-reject-errors */
import { onWarning, onError } from '@/plugins/alerts'

const errorCollection = {
  HTTP: {
    500: (error) => {
      onError('Внутренняя ошибка сервера')
      return Promise.reject(
        `${error.response.status} ${error.response.statusText}`
      )
    },

    413: (error) => {
      onError('Превышен объем загружаемых данных')
      return Promise.reject(
        `${error.response.status} ${error.response.statusText}`
      )
    },

    404: (error) => {
      onError(`Компонент ${error.response.config.url.split('/')[4]} не найден`)
      return Promise.reject({
        error: `${error.response.status} ${error.response.statusText}`,
        code: 404
      })
    },

    403: (error) => {
      onError('Доступ запрещен')
      return Promise.reject(
        `${error.response.status} ${error.response.statusText}`
      )
    },

    401: (error) => Promise.reject(error)
  },

  custom: {
    unhandledStatusCode: () => Promise.reject('Unhandled response status code'),

    0: () => {
      onError()
      return Promise.reject('Bad response from server')
    },

    101: () =>
      Promise.reject({
        error: 'Does not exist',
        code: 101
      }),

    102: () =>
      Promise.reject({
        error: 'Duplicate entry',
        code: 102
      }),

    103: () => {
      onWarning('Тип файла не поддерживается')
      return Promise.reject('Wrong file format')
    },

    104: () => {
      onWarning('Превышен лимит загружаемых файлов')
      return Promise.reject('Limit exceeded')
    },

    105: () => {
      onWarning('Не удалось загрузить файл')
      return Promise.reject('Did not upload')
    },

    106: () => {
      onWarning('Не прикреплен файл')
      return Promise.reject('File is required')
    }
  }
}

export const responseManage = (response) => {
  if ('error' in response.data) return errorManage(response)

  if (Array.isArray(response.data) && !response.data.length) { return Promise.resolve('Empty data') }

  if ('status' in response.data) {
    if (response.data.status in errorCollection.custom) { return errorCollection.custom[response.data.status]() }

    if (response.data.status === 1) return Promise.resolve(response)
  }

  return Promise.resolve(response)
}

export const errorManage = (error) => {
  if ('response' in error) {
    if (error.response.status in errorCollection.HTTP) { return errorCollection.HTTP[error.response.status](error) }
  }

  if (error.data.error) {
    const { data } = error

    if (data.status in errorCollection.custom) { return errorCollection.custom[data.status]() } else return Promise.reject(error.data)
  }

  Promise.reject(error)
}
