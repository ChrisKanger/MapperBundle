MapperBundle
============

Bundle for translating a response from a webservice to models. 

This bundle uses Doctrine 2 annotations. 
The annotations Object, Field, OneToOne and OneToMany make it extremly easy to map the result of a webservice to an object.
As an added feature this bundle has a mapper that does the exact opposite for creating soap-requests. Although this mapper is not yet complete it should allow you to transform to basic soap requests and then back using the SoapMapper.
