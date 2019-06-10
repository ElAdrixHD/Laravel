# LARAVEL - RESERVAS DE PADEL

### Explicación:

Este repositorio es una aplicación de laravel para las reservas de una pista de padel.

Se tiene en cuenta los siguientes requisitos:

1. Una pista no se puede reservar antes de las 9 AM y despues de las 11PM.
2. Como máximo solo se puede reservar 2h.
3. Solo puedes reservar un dia una sola vez.
4. Como máximo puedes reservar con antelación , 7 dias.

Todo esto se hace con un metodo que comprueba cada uno de los requisitos.

https://gist.github.com/ElAdrixHD/45722fb063051d1096b7f760da5a3547

Tambien se tiene en cuenta de que no se solapen las horas con otras reservas de otros usuarios.

En esa función, si hay errores se va guardando en un array y luego cuando el metodo acaba, se busca si el array devulto contiene datos. En caso afirmativo, no dejara reservar.

### Posibles mejoras:

1. Mejorar la interfaz gráfica de laravel.
2. Optimizar el codigo.
3. Añadir modo Admin a la aplicación.
