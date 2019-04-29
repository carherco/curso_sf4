Voters
======

Los voters son clases que deciden si un usuario puede acceder a un recurso o no.

Cada vez que se llama al método **isGranted()** o al método **denyAccessUnlessGranted()** Symfony hace una llamada a cada clase voter que haya registrada en el sistema.

Cada uno de los voters decidirá si permite al usuario realizar la acción, si le deniega realizarla o si se abstiene de decidir nada. Symfony recoge la respuesta de todos los Voters y toma la decisión final en base a la estrategia configurada.

La clase Voter
--------------

Un voter personalizado necesita implementar VoterInterface o extender Voter.

```php
abstract class Voter implements VoterInterface
{
    abstract protected function supports($attribute, $subject);
    abstract protected function voteOnAttribute($attribute, $subject, TokenInterface $token);
}
```

Nuestro voter será por lo tanto una clase con dos métodos:

- supports($attribute, $subject)
- voteOnAttribute($attribute, $subject, TokenInterface $token)

Vamos a suponer que tenemos una aplicación que gestiona eventos, y que los usuarios solamente pueden editar los eventos de los que son creadores.

Es decir, cualquier usuario puede editar eventos, pero solamente los suyos.

```php
    /**
     * @Route("/events/{id}/edit", name="events_edit")
     */
    public function editAction($id)
    {

        $evento = ...;

        // chequear acceso de edición del evento
        $this->denyAccessUnlessGranted('edit', $evento);

        // ...
    }
```

El método denyAccessUnlessGranted() (y pasaría también con isGranted()) llama al sistema de voters. Ahora mismo no hay voters que puedan juzgar si el usuario puede editar el evento, así que vamos a crear uno.

```php
// src/AppBundle/Security/EditarEventoVoter.php
namespace AppBundle\Security;

use AppBundle\Entity\Evento;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditarEventoVoter extends Voter
{
    protected function supports($atributo, $entidad)
    {
        // Este voter sólo toma decisiones sobre editar objetos Evento
        if ($entidad instanceof Evento && $atributo == 'edit') {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute($atributo, $entidad, TokenInterface $token)
    {
        $user = $token->getUser();

        //Gracias al método supports ya sabemos que $entidad es un Evento
        if($user->getId() == $entidad->getCreador()->getId()){
          return true;
        }

        return false;
    }
}
```

El método supports()
--------------------

La llamada a *$this->denyAccessUnlessGranted('edit', $evento)* admite dos parámetros.
Estos dos parámetros se le pasan a la función supports del voter.

Este método debe devolver true o false. Si devuelve false, el voter se *abstiene* de tomar ninguna decisión y el sistema de seguridad de symfony lo ignora.

Si por el contrario, el método supports devuelve true, entonces Symfony llamará al método voteOnAttribute().

El método voteOnAttribute()
---------------------------

El objetivo de este método también es muy simple. Si devuelve true, Symfony permitirá al usuario realizar la acción. Si devuelve false, Symfony denegará al usuario realizar la acción.

A este método le llegan dos parámetros con los dos valores con los que se llamó a $this->denyAccessUnlessGranted('edit', $evento) y un tercer parámetro con acceso al objeto user.

Si es necesario, se puede utilizar la inyección de dependencias (en el constructor) para acceder a cualquier otro servicio con lo que en un voter tenemos acceso a cualquier elemento de nuestra aplicación que necesitemos para tomar la decisión.

Configurar el voter
-------------------

Para inyectar el voter en la capa de seguridad de Symfony debemos declararlo como servicio y ponerle el tag *security.voter*:

```yml
# app/config/services.yml
services:
    app.post_voter:
        class: AppBundle\Security\EditarEventoVoter
        tags:
            - { name: security.voter }
        # pequeña mejora de rendimiento
        public: false
```

Este paso no será necesario si tenemos el autoconfigure a true.

Estrategia de decisión
----------------------

Normalmente tendremos un único voter.

Otras veces podemos tener varios voters, pero solamente un voter votará en cada ocasión y el resto se abstendrán.

Y en raras ocasiones tendremos varios voters tomando una misma decisión. Por ejemplo, se podría dar el caso de un voter compruebe si el usuario es miembro del grupo del cual está intentando ver información, y otro voter puede estar comprobando si el usuario tiene más de 18 años para acceder a dicho contenido. En una plataforma de películas online: Cierta película es de pago (hay que comprobar si el usuario es miembro de pago) y además es una película para mayores de 18 años (también hay que comprobarlo).

Por defecto, cuando varios voters tienen que votar si permiten o no un acceso, basta con que uno de ellos lo permita, para que el usuario logre el acceso.

Sin embargo se puede cambiar este comportamiento, llamado "strategy" desde el security.yml. Hay 3 posibles estrategias para elegir:

- affirmative (por defecto). Otorga acceso tan pronto como haya un voter que permita acceso.
- consensus. Otorga acceso si hay más voters garantizando acceso que denegándolo.
- unanimous. Sólo otorga acceso una vez que todos los voters garantizan acceso.

```yml
# app/config/security.yml
security:
    access_decision_manager:
        strategy: unanimous
```

En caso de que todos los voters se abstengan, symfony NO permite el acceso. Para cambiar este comportamiento (a partir de la versión 4.1) hay que configurar el access_decision_manager de la siguiente manera:

```php
security:
    access_decision_manager:
        strategy: unanimous
        allow_if_all_abstain: false
```

Ejemplos comunes de Voters:

- Solamente los dueños de una entidad pueden ver o editar dicha entidad.
- Solamente los miembros de un grupo pueden ver el contenido del grupo.
- Restricciones de acceso por edad, región u otra propiedad del usuario.
- Restricciones de acceso por IP (blacklists).

Soporte para ACL
----------------

El soporte para ACL ha sido eliminado en Symfony 4.0. Si se quiere trabajar con ACL existe un bundle preparado para ello: https://github.com/symfony/acl-bundle

En las nuevas versiones de symfony:

allow_if_all_abstain
--------------------

En caso de que todos los voters se abstengan, el comportamiento por defecto es denegar el acceso al usuario. Se puede configurar este comportamiento con la opción **allow_if_all_abstain**.

```yaml
security:
    access_decision_manager:
        strategy: unanimous
        allow_if_all_abstain: true
```

allow_if_equal_granted_denied
-----------------------------

En la estrategia *consensus*, en caso de empate entre voters que permitan el acceso y voters que denieguen el acceso, por defecto se permite el acceso. Se puede cambiar este comportamiento con la opción **allow_if_equal_granted_denied**

```yaml
security:
    access_decision_manager:
        strategy: consensus
        allow_if_equal_granted_denied: false
```

Voters del core de seguridad
----------------------------

- AuthenticatedVoter

Este voter soporta los atributos IS_AUTHENTICATED_FULLY, IS_AUTHENTICATED_REMEMBERED, e IS_AUTHENTICATED_ANONYMOUSLY y da acceso basándose en el nivel actual de autenticación.

- RoleVoter

Este voter soporta los atributos que empiezen con ROLE_ y da acceso al usuario si dicho atributo se encuentra en el array de roles devuelto por el método getRoles() del token.

- RoleHierarchyVoter

Este voter extiende de RoleVoter y sabe como manejar la jerarquía de roles.
